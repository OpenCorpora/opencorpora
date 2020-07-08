<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

set_include_path(get_include_path().PATH_SEPARATOR.'/corpus');
require_once('lib/header_ajax.php');
require_once('lib/constants.php');
require_once('lib/lib_annot.php');
update();


function update() {
    $res = sql_query("
        SELECT rev_text, sent_id, source
        FROM tf_revisions
            LEFT JOIN tokens USING (tf_id)
            LEFT JOIN sentences USING (sent_id)
        WHERE is_last = 1
        ORDER BY sent_id
    ");
    $stats = array();
    $last_sent_id = 0;
    $has_unkn = false;
    $has_ambig = false;
    $word_count = 0;
    while ($row = sql_fetch_array($res)) {
        $sent_id = $row['sent_id'];
        if ($sent_id != $last_sent_id) {
            if ($last_sent_id > 0) {
                // print "words: $word_count, unkn: ".(int)$has_unkn.", ambig: ".(int)$has_ambig."\n";
                if (!isset($stats[$word_count])) {
                    $stats[$word_count] = array(
                        SENTENCE_QUALITY_NONE => 0,
                        SENTENCE_QUALITY_NO_AMBIG => 0,
                        SENTENCE_QUALITY_NO_AMBIG_OR_UNKN => 0,
                    );
                }

                if ($has_ambig) {
                    $stats[$word_count][SENTENCE_QUALITY_NONE] += 1;
                } else if ($has_unkn) {
                    $stats[$word_count][SENTENCE_QUALITY_NO_AMBIG] += 1;
                } else {
                    $stats[$word_count][SENTENCE_QUALITY_NO_AMBIG_OR_UNKN] += 1;
                }
                // reset
                $has_unkn = false;
                $has_ambig = false;
                $word_count = 0;
            }
            // print "$sent_id\t" . $row['source']."\n";
        }

        $pset = new MorphParseSet($row['rev_text']);
        if ($pset->is_unknown()) {
            $has_unkn = true;
            ++$word_count;
        } else if (count($pset->parses) > 1) {
            $has_ambig = true;
            ++$word_count;
        } else {
            $gram = $pset->parses[0]->gramlist[0]['inner'];
            if (!in_array($gram, array('PNCT', 'SYMB', 'NUMB', 'TIME', 'DATE'))) {
                ++$word_count;
            }
        }

        $last_sent_id = $sent_id;
    }

    // print "words: $word_count, unkn: $has_unkn, ambig: $has_ambig\n";
    if ($has_ambig) {
        $stats[$word_count][SENTENCE_QUALITY_NONE] += 1;
    } else if ($has_unkn) {
        $stats[$word_count][SENTENCE_QUALITY_NO_AMBIG] += 1;
    } else {
        $stats[$word_count][SENTENCE_QUALITY_NO_AMBIG_OR_UNKN] += 1;
    }

    // print_r($stats);
    sql_begin();
    sql_query("TRUNCATE TABLE sentence_quality");
    foreach ($stats as $len => $data) {
        foreach ($data as $status => $count) {
            sql_pe("
                INSERT INTO sentence_quality SET length = ?, status = ?, count = ?
            ", array($len, $status, $count));
        }
    }
    sql_commit();
}
