<?php
require_once('constants.php');

function get_sentence($sent_id) {
    $r = sql_fetch_array(sql_query("SELECT `check_status`, source FROM sentences WHERE sent_id=$sent_id LIMIT 1"));
    $out = array(
        'id' => $sent_id,
        'next_id' => get_next_sentence_id($sent_id),
        'prev_id' => get_previous_sentence_id($sent_id),
        'status' => $r['check_status'],
        'source' => $r['source']
    );
    //counting comments
    $r = sql_fetch_array(sql_query("SELECT COUNT(comment_id) comm_cnt FROM sentence_comments WHERE sent_id=$sent_id"));
    $out['comment_count'] = $r['comm_cnt'];
    //looking for source name
    $r = sql_fetch_array(sql_query("
        SELECT book_id, old_syntax_moder_id
        FROM books
        WHERE book_id = (
            SELECT book_id
            FROM paragraphs
            WHERE par_id = (
                SELECT par_id
                FROM sentences
                WHERE sent_id=$sent_id
                LIMIT 1
            )
        )
    "));
    $out['book_id'] = $book_id = $r['book_id'];
    $out['syntax_moder_id'] = $r['old_syntax_moder_id'];
    $r = sql_fetch_array(sql_query("
        SELECT book_name
        FROM books
        WHERE book_id = (
            SELECT parent_id
            FROM books
            WHERE book_id = $book_id
            LIMIT 1
        )
    "));
    $out['book_name'] = $r['book_name'];
    //looking for url
    $res = sql_query("
        SELECT tag_name
        FROM book_tags
        WHERE book_id = ".$book_id
    );
    while ($r = sql_fetch_array($res)) {
        if (substr($r['tag_name'], 0, 4) == 'url:') {
            $out['url'] = substr($r['tag_name'], 4);
            break;
        }
    }
    $tf_text = array();
    // TODO we'd better preload all grammemes info to save queries
    $res = sql_query("
        SELECT tf_id, tf_text, rev_text
        FROM tokens
        LEFT JOIN tf_revisions
            USING (tf_id)
        WHERE sent_id=$sent_id
        AND is_last = 1
        ORDER BY `pos`
    ");
    $j = 0; //token position, for further highlighting
    $gram_descr = array();  //associative array to keep info about grammemes
    while ($r = sql_fetch_array($res)) {
        array_push($tf_text, '<span id="src_token_'.($j++).'">'.htmlspecialchars($r['tf_text']).'</span>');
        $arr = xml2ary($r['rev_text']);

        $out['tokens'][] = array(
            'tf_id'        => $r['tf_id'],
            'tf_text'      => $r['tf_text'],
            'variants'     => get_morph_vars($arr['tfr']['_c']['v'], $gram_descr)
        );
    }
    $out['fulltext'] = typo_spaces(implode(' ', $tf_text), 1);
    return $out;
}
function get_previous_sentence_id($sent_id) {
    return get_adjacent_sentence_id($sent_id, false);
}
function get_next_sentence_id($sent_id) {
    return get_adjacent_sentence_id($sent_id, true);
}
function get_adjacent_sentence_id($sent_id, $next) {
    // same paragraph
    $r = sql_fetch_array(sql_query("
        SELECT par_id, pos
        FROM sentences
        WHERE sent_id = $sent_id LIMIT 1
    "));

    $par_id = $r['par_id'];
    $sent_pos = $r['pos'];
    if (!$par_id)
        throw new Exception();

    $res = sql_query("
        SELECT sent_id
        FROM sentences
        WHERE par_id = $par_id
        AND pos ".($next ? ">" : "<")." $sent_pos
        ORDER BY pos ".($next ? "ASC" : "DESC")."
        LIMIT 1
    ");

    if (sql_num_rows($res) == 1) {
        $r = sql_fetch_array($res);
        return $r['sent_id'];
    }

    // next/previous paragraph
    $r = sql_fetch_array(sql_query("
        SELECT book_id, pos
        FROM paragraphs
        WHERE par_id = $par_id LIMIT 1
    "));

    $book_id = $r['book_id'];
    $par_pos = $r['pos'];

    if (!$book_id)
        throw new Exception();

    $res = sql_query("
        SELECT sent_id
        FROM sentences s
        JOIN paragraphs p
            USING (par_id)
        JOIN books b
            USING (book_id)
        WHERE book_id = $book_id
        AND p.pos ".($next ? ">" : "<")." $par_pos
        ORDER BY p.pos ".($next ? "ASC" : "DESC").", s.pos ".($next ? "ASC" : "DESC")."
        LIMIT 1
    ");

    if (sql_num_rows($res) == 1) {
        $r = sql_fetch_array($res);
        return $r['sent_id'];
    }

    return 0;
}
function get_morph_vars($xml_arr, &$gram_descr) {
    if (isset($xml_arr['_c']) && is_array($xml_arr['_c'])) {
        //the only variant
        $var = get_morph_vars_inner($xml_arr, 1);
        $t = array();
        foreach ($var['gram_list'] as $gr) {
            if (!isset($gram_descr[$gr['inner']])) {
                $r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='".$gr['inner']."' COLLATE utf8_bin LIMIT 1"));
                $gram_descr[$gr['inner']] = array($r[0], $r[1]);
            }
            $t[] = array('inner' => $gr['inner'], 'outer' => $gram_descr[$gr['inner']][0], 'descr' => $gram_descr[$gr['inner']][1]);
        }
        $var['gram_list'] = $t;
        return array($var);
    } else {
        //multiple variants
        $out = array();
        $i = 1;
        if (is_array($xml_arr)) {
            foreach ($xml_arr as $xml_var_arr) {
                $var = get_morph_vars_inner($xml_var_arr, $i++);
                $t = array();
                foreach ($var['gram_list'] as $gr) {
                    if (!isset($gram_descr[$gr['inner']])) {
                        $r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='".$gr['inner']."' COLLATE utf8_bin LIMIT 1"));
                        $gram_descr[$gr['inner']] = array($r[0], $r[1]);
                    }
                    $t[] = array('inner' => $gr['inner'], 'outer' => $gram_descr[$gr['inner']][0], 'descr' => $gram_descr[$gr['inner']][1]);
                }
                $var['gram_list'] = $t;
                $out[] = $var;
            }
        }
        return $out;
    }
}
function get_morph_vars_inner($xml_arr, $num) {
    $lemma_grm = $xml_arr['_c']['l']['_c']['g'];
    $grm_arr = array();
    if (isset ($lemma_grm['_a']) && is_array($lemma_grm['_a'])) {
        $grm_arr[] = array('inner' => $lemma_grm['_a']['v']);
    } elseif (is_array($lemma_grm)) {
        foreach ($lemma_grm as $t) {
            $grm_arr[] = array('inner' => $t['_a']['v']);
        }
    }
    return array(
        'num'        => $num,
        'lemma_id'   => $xml_arr['_c']['l']['_a']['id'],
        'lemma_text' => $xml_arr['_c']['l']['_a']['t'],
        'gram_list'  => $grm_arr
    );
}
function sentence_save($sent_id) {
    if (!$sent_id)
        throw new UnexpectedValueException();
    $flag = $_POST['var_flag'];  //what morphovariants are checked as possible (array of arrays)
    $dict = $_POST['dict_flag']; //whether this token has been reloaded from the dictionary (array)

    $res = sql_query("
        SELECT tf_id, tf_text, rev_text
        FROM tokens
        LEFT JOIN tf_revisions
            USING (tf_id)
        WHERE sent_id=$sent_id
        AND is_last = 1
        ORDER BY `pos`
    ");
    while ($r = sql_fetch_array($res)) {
        $tokens[$r['tf_id']] = array($r['tf_text'], $r['rev_text']);
    }
    $matches = array();
    $all_changes = array();
    if (count($flag) != count($tokens))
        throw new Exception();

    sql_begin();
    foreach ($tokens as $tf_id=>$v) {
        list($tf_text, $base_xml) = $v;
        //substitute the last revision's xml for one from dictionary if relevant
        if ($dict[$tf_id] == 1) {
            $xml = generate_tf_rev($tf_text);
        } else {
            $xml = $base_xml;
        }
        $new_xml = "<tfr t=\"".htmlspecialchars($tf_text)."\">";
        //let's find all vars inside tf_text
        if (preg_match_all("/<v>(.+?)<\/v>/", $xml, $matches) !== false) {
            //flags quantity check
            if (count($matches[1]) != count($flag[$tf_id]))
                throw new Exception();

            $not_empty = 0;
            foreach ($flag[$tf_id] as $k=>$f) {
                if ($f == 1) {
                    $not_empty = 1;
                    $new_xml .= '<v>'.$matches[1][$k-1].'</v>'; //attention to -1
                }
            }
            //inserting UnknownPOS if no variants present
            if (!$not_empty) {
                $new_xml .= '<v><l id="0" t="'.htmlspecialchars(mb_strtolower($tf_text, 'UTF-8')).'"><g v="UNKN"/></l></v>';
            }
            $new_xml .= '</tfr>';
            if ($base_xml != $new_xml) {
                //something's changed
                array_push($all_changes, array($tf_id, $new_xml));
            }
        } else {
            throw new Exception();
        }
    }
    if (count($all_changes) > 0) {
        $revset_id = create_revset($_POST['comment']);
        foreach ($all_changes as $v)
            create_tf_revision($revset_id, $v[0], $v[1]);
    }
    sql_query("UPDATE sentences SET check_status='1' WHERE sent_id=$sent_id LIMIT 1");
    sql_commit();
}
function sentence_save_source($sent_id, $text) {
    sql_pe("UPDATE sentences SET source = ? WHERE sent_id=? LIMIT 1", array(trim($text), $sent_id));
}
function create_tf_revision($revset_id, $token_id, $rev_xml) {
    $res = sql_pe("SELECT rev_text FROM tf_revisions WHERE tf_id=? ORDER BY rev_id DESC LIMIT 1", array($token_id));
    if (sizeof($res) > 0 && $res[0]['rev_text'] === $rev_xml)
        // revisions are identical, do nothing
        return true;
    sql_begin();
    sql_pe("UPDATE tf_revisions SET is_last=0 WHERE tf_id=?", array($token_id));
    sql_pe("INSERT INTO `tf_revisions` VALUES(NULL, ?, ?, ?, 1)", array($revset_id, $token_id, $rev_xml));
    sql_commit();
}
// annotation pools
function get_morph_pool_types($extended=false) {
    $res = sql_query("SELECT type_id, grammemes, complexity, doc_link FROM morph_annot_pool_types order by grammemes");
    $types = array();
    while ($r = sql_fetch_array($res))
        if ($extended)
            $types[$r['type_id']] = array(
                'grammemes' => $r['grammemes'],
                'complexity' => $r['complexity'],
                'doc_link' => $r['doc_link']
            );
        else
            $types[$r['type_id']] = $r['grammemes'];
    return $types;
}
function save_morph_pool_types($data) {
    sql_begin();
    $upd = sql_prepare("UPDATE morph_annot_pool_types SET complexity=?, doc_link=? WHERE type_id=? LIMIT 1");
    foreach ($data['complexity'] as $id => $level) {
        if ($id <= 0 || $level < 0 || $level > 4 || !isset($data['doc'][$id]))
            throw new UnexpectedValueException();
        sql_execute($upd, array($level, $data['doc'][$id], $id));
    }
    sql_commit();
}
function get_morph_pools_page($type, $moder_id=0, $filter=false) {
    $pools = array();
    $instance_count = array();
    $moderators = array(0 => '-- Модератор --');

    // possible pool types for addition form
    $types = get_morph_pool_types();
    $types[0] = 'Новый';

    // possible moderators for filter
    $res = sql_query("SELECT DISTINCT moderator_id, user_shown_name AS user_name FROM morph_annot_pools p LEFT JOIN users u ON (p.moderator_id = u.user_id) WHERE moderator_id > 0 ORDER BY user_shown_name");
    while ($r = sql_fetch_array($res))
        $moderators[$r['moderator_id']] = $r['user_name'];

    // count instances in one query and preserve
    $res = sql_pe("SELECT answer, count(instance_id) cnt, pool_id FROM morph_annot_instances LEFT JOIN morph_annot_samples s USING(sample_id) WHERE pool_id IN (SELECT pool_id FROM morph_annot_pools WHERE status = ?) GROUP BY (answer > 0), pool_id ORDER BY pool_id", array($type));
    foreach ($res as $r) {
        if (!isset($instance_count[$r['pool_id']]))
            $instance_count[$r['pool_id']] = array(0, 0, 0);

        if ($r['answer'] > 0)
            $instance_count[$r['pool_id']][0] += $r['cnt'];
        $instance_count[$r['pool_id']][1] += $r['cnt'];
    }
    // and moderated answers if needed
    if ($type == MA_POOLS_STATUS_MODERATION) {
        $res = sql_query("SELECT pool_id, COUNT(*) cnt FROM morph_annot_moderated_samples LEFT JOIN morph_annot_samples USING(sample_id) WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id IN (SELECT pool_id FROM morph_annot_pools WHERE status=".MA_POOLS_STATUS_MODERATION.")) AND answer > 0 GROUP BY pool_id");
        while ($r = sql_fetch_array($res)) {
            $instance_count[$r['pool_id']][2] = $r['cnt'];
        }
    }

    $q_moder = '';
    if ($moder_id > 0)
        $q_moder = "AND p.moderator_id = $moder_id";

    $q_filter = '';
    if ($filter)
        $q_filter = "AND t.grammemes REGEXP ".sql_quote($filter);

    $res = sql_pe("SELECT p.*, t.grammemes, t.gram_descr, u1.user_shown_name AS author_name, u2.user_shown_name AS moderator_name FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) LEFT JOIN users u1 ON (p.author_id = u1.user_id) LEFT JOIN users u2 ON (p.moderator_id = u2.user_id) WHERE status = ? $q_moder $q_filter ORDER BY p.updated_ts DESC", array($type));
    foreach ($res as $r) {
        if ($type == MA_POOLS_STATUS_FOUND_CANDIDATES) {
            $r1 = sql_fetch_array(sql_query("SELECT COUNT(*) FROM morph_annot_candidate_samples WHERE pool_id=".$r['pool_id']));
            $r['candidate_count'] = $r1[0];
        }
        elseif ($type == MA_POOLS_STATUS_MODERATION) {
            $r['moderated_count'] = $instance_count[$r['pool_id']][2];
        }

        $r['answer_count'] = $instance_count[$r['pool_id']][0];
        $r['instance_count'] = $instance_count[$r['pool_id']][1];

        $pools[] = $r;
    }
    return array('pools' => $pools, 'types' => $types, 'moderators' => $moderators);
}
function get_morph_samples_page($pool_id, $extended=false, $context_width=4, $skip=0, $filter=false, $samples_by_page=0) {
    $res = sql_pe("
        SELECT pool_name, pool_type, status, t.grammemes, t.has_focus, t.doc_link,
            users_needed, moderator_id, user_shown_name AS user_name
        FROM morph_annot_pools p
        LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id)
        LEFT JOIN users ON (p.moderator_id = users.user_id)
        WHERE pool_id=?
        LIMIT 1", array($pool_id));
    $pool_gram = explode('@', str_replace('&', ' & ', $res[0]['grammemes']));
    $select_options = array('---');
    foreach ($pool_gram as $v) {
        $select_options[] = $v;
    }
    $select_options[99] = 'Other';
    $out = array(
        'id' => $pool_id,
        'type' => $res[0]['pool_type'],
        'variants' => $select_options,
        'name' => $res[0]['pool_name'],
        'status' => $res[0]['status'],
        'has_manual' => (bool)$res[0]['doc_link'],
        'num_users' => $res[0]['users_needed'],
        'moderator_name' => $res[0]['user_name'],
        'has_focus' => $res[0]['has_focus'],
        'samples' => array()
    );
    $res = sql_pe("
        SELECT sample_id, s.tf_id
        FROM morph_annot_samples s
        LEFT JOIN tokens f ON (s.tf_id = f.tf_id)
        WHERE pool_id=?
        ORDER BY tf_text, sample_id
    ", array($pool_id));
    $gram_descr = array();
    $distinct_users = array();
    $out['all_moderated'] = $extended ? true : false;  // for now we never get active button with non-extended view, just for code simplicity
    $num_samples = sizeof($res);
    $out['pages'] = array(
        'active' => $samples_by_page ? ($skip / $samples_by_page) : 0,
        'query' => preg_replace('/&skip=\d+/', '', $_SERVER['QUERY_STRING']),
        'total' => 0
    );
    foreach ($res as $r) {
        $t = get_context_for_word($r['tf_id'], $context_width);
        $t['id'] = $r['sample_id'];
        $t['token_id'] = $r['tf_id'];
        $r1 = sql_fetch_array(sql_query("SELECT book_id FROM paragraphs WHERE par_id = (SELECT par_id FROM sentences WHERE sent_id = ".$t['sentence_id']." LIMIT 1) LIMIT 1"));
        $t['book_id'] = $r1['book_id'];
        $r1 = sql_fetch_array(sql_query("SELECT COUNT(*) FROM morph_annot_instances WHERE sample_id=".$r['sample_id']." AND answer>0"));
        $t['answered'] = $r1[0];
        if ($extended) {
            $r1 = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id = ".$r['tf_id']." AND is_last=1 LIMIT 1"));
            $arr = xml2ary($r1['rev_text']);
            $t['parses'] = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
            $res1 = sql_query("SELECT instance_id, user_id, answer FROM morph_annot_instances WHERE sample_id=".$r['sample_id']." ORDER BY instance_id");
            $disagreement_flag = 0;
            $not_ok_flag = false;
            $vars = '';
            while ($r1 = sql_fetch_array($res1)) {
                if ($r1['answer'] == 99)
                    $disagreement_flag = 1;
                elseif (!$vars)
                    $vars = $r1['answer'];
                elseif ($r1['answer'] && $vars != $r1['answer'])
                    $disagreement_flag = 1;
                //about users
                if (!isset($distinct_users[$r1['user_id']])) {
                    $r2 = sql_fetch_array(sql_query("SELECT user_shown_name AS user_name FROM users WHERE user_id=".$r1['user_id']." LIMIT 1"));
                    $distinct_users[$r1['user_id']] = array(sizeof($distinct_users), $r2['user_name']);
                }
                //push
                $t['instances'][] = array(
                    'id' => $r1['instance_id'],
                    'answer_num' => $r1['answer'],
                    'answer_gram' => ($r1['answer'] > 0 && $r1['answer'] < 99) ? $pool_gram[$r1['answer']-1] : '',
                    'user_id' => $r1['user_id'],
                    'user_color' => $distinct_users[$r1['user_id']][0]
                );
            }
            $t['disagreed'] = $disagreement_flag;
            $t['comments'] = get_sample_comments($r['sample_id']);
            //for moderators
            if ($out['status'] > MA_POOLS_STATUS_ANSWERED) {
                $r1 = sql_fetch_array(sql_query("SELECT answer, status FROM morph_annot_moderated_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1"));
                $t['moder_answer_num'] = $r1['answer'];
                $t['moder_status_num'] = $r1['status'];
                if ($r1['status'] > 0)
                    $not_ok_flag = true;
                if ($t['moder_answer_num'] == 0)
                    $out['all_moderated'] = false;
                else {
                    $t['moder_answer_gram'] = ($r1['answer'] == 99 ? 'Other' : $pool_gram[$r1['answer']-1]);
                    // highlight samples where the moderator disagreed with all the annotators
                    if (!$t['disagreed'] && $t['moder_answer_num'] != $t['instances'][0]['answer_num'])
                        $t['disagreed'] = 2;
                }
            }
        }

        // to add or not to add
        $add = false;
        $m = NULL;

        if (!$extended || !$filter)
            $add = true;
        elseif (preg_match('/^user:(\d+)$/', $filter, $m)) {
            foreach ($t['instances'] as $answer) {
                if ($answer['user_id'] == $m[1] && $answer['answer_num'] != $t['moder_answer_num'])
                    $add = true;
            }
        }
        elseif ($filter == 'focus' && (
                    $t['disagreed'] ||
                    sizeof($t['comments']) > 0 ||
                    filter_sample_for_moderation($out['type'], $t, $out['has_focus'])
                ))
            $add = true;
        elseif (
            ($filter != 'focus' && (
            ($t['disagreed'] && $filter == 'disagreed') ||
            ($out['status'] > MA_POOLS_STATUS_ANSWERED && $t['moder_answer_num'] == 0 && $filter == 'not_moderated') ||
            (sizeof($t['comments']) > 0 && $filter == 'comments') ||
            ($not_ok_flag && $filter == 'not_ok')
            ))
        )
            $add = true;

        if ($add) {
            if ($skip > 0)
                --$skip;
            elseif ($samples_by_page == 0 || sizeof($out['samples']) < $samples_by_page)
                $out['samples'][] = $t;

            $out['pages']['total'] += 1;
        }
    }
    $out['user_colors'] = $distinct_users;
    $out['filter'] = $filter;
    $out['pages']['total'] = $samples_by_page ? ceil($out['pages']['total'] / $samples_by_page) : 1;
    return $out;
}
function filter_sample_for_moderation($pool_type, $sample, $has_focus) {
    $mainword = $sample['context'][$sample['mainword']];

    // check all focus words beginning with a capital letter
    $first_letter = mb_substr($mainword, 0, 1);
    if (mb_strtoupper($first_letter) === $first_letter)
        return true;

    // check all one-symbol focus words except aux parts of speech
    if (
        !in_array($pool_type, array(35, 36, 44, 70)) &&
        mb_strlen($mainword) == 1
    )
        return true;

    // disregard context in any pools except the following
    if (!$has_focus)
        return false;

    // ADJF masc/neut
    if ($pool_type == 2) {
        if (preg_match('/^общем$/iu', $mainword))
            return true;
        if (isset($sample['context'][$sample['mainword'] + 1]) &&
            mb_strlen($sample['context'][$sample['mainword'] + 1]) == 1)
            return true;
        return false;
    }

    // NOUN/PREP
    if ($pool_type == 35) {
        if (preg_match('/^(?:посредством|типа)$/iu', $mainword))
            return true;
        if (isset($sample['context'][$sample['mainword'] - 1]) &&
            (
                preg_match('/^только$/iu', $sample['context'][$sample['mainword'] - 1]) ||
                mb_strlen($sample['context'][$sample['mainword'] - 1]) == 2
            ))
            return true;
        return false;
    }

    // GRND/PREP
    if ($pool_type == 36) {
        if (preg_match('/^(?:включая|благодаря)$/iu', $mainword))
            return true;
        if (isset($sample['context'][$sample['mainword'] - 1]) &&
            mb_strlen($sample['context'][$sample['mainword'] - 1]) == 1)
            return true;
        return false;
    }

    // CONJ/INTJ
    if ($pool_type == 44) {
        if (preg_match('/^однако$/iu', $mainword))
            return true;
        if (isset($sample['context'][$sample['mainword'] + 1]) &&
            mb_strlen($sample['context'][$sample['mainword'] + 1]) == 1)
            return true;
        return false;
    }

    // INTJ/PREP
    if ($pool_type == 70) {
        if (isset($sample['context'][$sample['mainword'] - 1]) &&
            $sample['context'][$sample['mainword'] - 1] == '-')
            return true;
        if (isset($sample['context'][$sample['mainword'] + 1]) &&
            mb_strlen($sample['context'][$sample['mainword'] + 1]) == 1)
            return true;
        return false;
    }

    // therefore it is 12 (NOUN sing/plur)

    // focus word with Fixd or Pltm
    foreach ($sample['parses'] as $parse) {
        foreach ($parse['gram_list'] as $gram) {
            if (in_array($gram['inner'], array('Fixd', 'Pltm')))
                return true;
        }
    }

    // left or right context with numbers
    // except 'NNNN goda'
    if (
        preg_match('/^года$/iu', $mainword) &&
        isset($sample['context'][$sample['mainword'] - 1]) &&
        preg_match('/^[0-9]{4}$/', $sample['context'][$sample['mainword'] - 1])
    )
        return false;

    for ($i = max(0, $sample['mainword'] - 3); $i < min($sample['mainword'] + 3, sizeof($sample['context'])); ++$i) {
        if ($i == $sample['mainword'])
            continue;
        if (preg_match('/^(?:\d+|полтор[аы]|дв[ае]|об[ае]|три|четыре)$/iu', $sample['context'][$i]))
            return true;
    }

    // nothing suspicious, ok
    return false;
}
function get_pool_candidates_page($pool_id) {
    $pool = array('id' => $pool_id);
    $res = sql_pe("SELECT pool_name FROM morph_annot_pools WHERE pool_id=? LIMIT 1", array($pool_id));
    $pool['name'] = $res[0]['pool_name'];
    $matches = array();
    if (preg_match('/^(.+?)\s+#(\d+)/', $pool['name'], $matches))
        $pool['next_name'] = $matches[1] . ' #';
    else
        $pool['next_name'] = $pool['name'] . ' #';
    $pool['samples'] = get_pool_candidates($pool_id);
    return $pool;
}
function get_pool_candidates($pool_id) {
    $res = sql_pe("SELECT tf_id FROM morph_annot_candidate_samples WHERE pool_id=? ORDER BY RAND() LIMIT 200", array($pool_id));
    $out = array();
    $prep_query = NULL;
    foreach ($res as $r) {
        $out[] = get_context_for_word($r[0], 2, 0, 1, $prep_query);
    }
    return $out;
}
function get_context_for_word($tf_id, $delta, $dir=0, $include_self=1, &$prepared_queries=NULL) {
    // dir stands for direction (-1 => left, 1 => right, 0 => both)
    // delta <= 0 stands for infinity
    $t = array();
    $tw = 0;
    $left_c = -1;  //if there is left context to be added
    $right_c = 0;  //same for right context
    $mw_pos = 0;
    
    // prepare the 1st query
    if ($prepared_queries === NULL)
        $prepared_queries = array(sql_prepare("
            SELECT MAX(tokens.pos) AS maxpos, MIN(tokens.pos) AS minpos, sent_id, source, book_id
            FROM tokens
                JOIN sentences USING (sent_id)
                JOIN paragraphs USING (par_id)
            WHERE sent_id = (
                SELECT sent_id
                FROM tokens
                WHERE tf_id=? LIMIT 1
            )
        "));

    sql_execute($prepared_queries[0], array($tf_id));
    $res = sql_fetchall($prepared_queries[0]);
    $r = $res[0];
    $sent_id = $r['sent_id'];
    $sentence_text = $r['source'];
    $book_id = $r['book_id'];
    $maxpos = $r['maxpos'];
    $minpos = $r['minpos'];

    // prepare the 2nd query
    // this is really bad unreadable code, sorry
    if (sizeof($prepared_queries) == 1) {
        $q = "SELECT tf_id, tf_text, pos FROM tokens WHERE sent_id = ?";
        if ($dir != 0 || $delta > 0) {
            $q_left = $dir <= 0 ? ($delta > 0 ? "(SELECT IF(pos > $delta, pos - $delta, 0) FROM tokens WHERE tf_id=? LIMIT 1)" : "0") : "(SELECT pos FROM tokens WHERE tf_id=? LIMIT 1)";
            $q_right = $dir >= 0 ? ($delta > 0 ? "(SELECT pos+$delta FROM tokens WHERE tf_id=? LIMIT 1)" : "1000") : "(SELECT pos FROM tokens WHERE tf_id=? LIMIT 1)";
            $q .= " AND pos BETWEEN $q_left AND $q_right";
        }

        $q .= " ORDER BY pos";
        $prepared_queries[] = sql_prepare($q);
    }

    // how many values should we provide?
    $bound = array($tf_id, $tf_id);
    if ($delta <= 0) {
        if ($dir == 0)
            $bound = array();
        else
            $bound = array($tf_id);
    }

    sql_execute($prepared_queries[1], array_merge(array($sent_id), $bound));

    foreach (sql_fetchall($prepared_queries[1]) as $r) {
        if ($delta > 0) {
            if ($left_c == -1) {
                $left_c = ($r['pos'] == $minpos) ? 0 : $r['tf_id'];
            }
            if ($mw_pos) {
                if ($r['pos'] > $mw_pos)
                    $right_c = $r['tf_id'];
                if ($right_c && $r['pos'] == $maxpos)
                    $right_c = 0;
            }
        }

        if ($include_self || $r['tf_id'] != $tf_id)
            $t[] = $r['tf_text'];
        if ($include_self && $r['tf_id'] == $tf_id) {
            $tw = sizeof($t) - 1;
            $mw_pos = $r['pos'];
        }
    }
    return array(
        'context' => $t,
        'mainword' => $tw,
        'has_left_context' => $left_c,
        'has_right_context' => $right_c,
        'sentence_id' => $sent_id,
        'sentence_text' => $sentence_text,
        'book_id' => $book_id
    );
}
function add_morph_pool_type($post_gram, $post_descr) {
    $gram_sets = array();
    $gram_descr = array();
    foreach ($post_gram as $i => $gr) {
        if (!trim($gr))
            break;
        if (strpos($gr, '@') !== false)
            throw new UnexpectedValueException();
        $gram_sets[] = str_replace(' ', '', trim($gr));
        $gram_descr[] = trim($_POST['descr'][$i]);
    }

    if (sizeof($gram_sets) < 2)
        throw new UnexpectedValueException();

    $gram_sets_str = join('@', $gram_sets);
    $gram_descr_str = join('@', $gram_descr);

    sql_pe("INSERT INTO morph_annot_pool_types VALUES (NULL, ?, ?, '', 0, 0, 0)", array($gram_sets_str, $gram_descr_str));
    return sql_insert_id();
}
function add_morph_pool() {
    $pool_name = trim($_POST['pool_name']);
    $pool_type = $_POST['pool_type'];
    sql_begin();
    if (!$pool_type)
        $pool_type = add_morph_pool_type($_POST['gram'], $_POST['descr']);

    $users = $_POST['users_needed'];
    $token_check = $_POST['token_checked'];
    $ts = time();
    sql_pe(
        "INSERT INTO morph_annot_pools VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0)",
        array($pool_type, $pool_name, $token_check, $users, $ts, $ts, $_SESSION['user_id'])
    );
    sql_commit();
}
function delete_morph_pool($pool_id) {
    //NB: we mustn't delete any pools with answers
    $res = sql_pe("SELECT instance_id FROM morph_annot_instances WHERE answer > 0 AND sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=?) LIMIT 1", array($pool_id));
    if (sizeof($res) > 0)
        throw new Exception("Пул содержит пользовательские ответы");

    sql_begin();
    sql_pe("DELETE FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=?)", array($pool_id));
    sql_pe("DELETE FROM morph_annot_candidate_samples WHERE pool_id=?", array($pool_id));
    sql_pe("DELETE FROM morph_annot_moderated_samples WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=?)", array($pool_id));
    sql_pe("DELETE FROM morph_annot_samples WHERE pool_id=?", array($pool_id));
    sql_pe("DELETE FROM morph_annot_pools WHERE pool_id=? LIMIT 1", array($pool_id));
    sql_commit();
}
function promote_samples_aux($tf_ids, $orig_pool_id, $lastrev, $new_pool_name, &$new_pool_index, &$promoted_pools) {
    sql_begin();
    $time = time();

    // the first pool
    if (!$promoted_pools) {
        $current_pool_id = $orig_pool_id;
    }
    // all except the first
    else {
        $full_name = $new_pool_name . ' #' . ($new_pool_index++);
        sql_pe(
            "INSERT INTO morph_annot_pools (SELECT NULL, pool_type, ".sql_quote($full_name).", token_check, users_needed, $time, $time, author_id, 0, ".MA_POOLS_STATUS_NOT_STARTED.", $lastrev FROM morph_annot_pools WHERE pool_id=? LIMIT 1)",
            array($orig_pool_id)
        );
        $current_pool_id = sql_insert_id();
    }

    $lsoval = array();
    foreach ($tf_ids as $id)
        $lsoval[] = '(' . join(', ', array('NULL', $current_pool_id, $id)) . ')';

    sql_query("INSERT INTO morph_annot_samples VALUES ".join(', ', $lsoval));

    $promoted_pools[] = $current_pool_id;

    sql_commit();
}
function delete_samples_by_token_id($token_id) {
    $res = sql_pe("
        SELECT sample_id, answer
        FROM morph_annot_samples
        LEFT JOIN morph_annot_instances USING (sample_id)
        WHERE tf_id=?
        ORDER BY sample_id
    ", array($token_id));
    $last_sid = 0;
    $has_answer = false;
    sql_begin();
    foreach ($res as $r) {
        if ($last_sid != $r['sample_id']) {
            if ($last_sid && !$has_answer)
                delete_sample($last_sid);
            $has_answer = false;
        }
        if ($r['answer'] > 0)
            $has_answer = true;
        $last_sid = $r['sample_id'];
    }

    if ($last_sid && !$has_answer)
        delete_sample($last_sid);

    sql_commit();
}
function delete_sample($sample_id) {
    sql_begin();
    sql_pe("DELETE FROM morph_annot_instances WHERE sample_id=?", array($sample_id));
    sql_pe("DELETE FROM morph_annot_moderated_samples WHERE sample_id=? LIMIT 1", array($sample_id));
    sql_pe("DELETE FROM morph_annot_samples WHERE sample_id=? LIMIT 1", array($sample_id));
    sql_commit();
}
function promote_samples($pool_id, $type) {
    $pools_num = (int)$_POST['pools_num'];
    if (!$pool_id || !$type || !$pools_num)
        throw new UnexpectedValueException();

    $cond = "WHERE pool_id=?";

    switch ($type) {
        case 'first':
            $n = (int)$_POST['first_n'];
            $cond .= " ORDER BY tf_id LIMIT " . ($n * $pools_num);
            break;
        case 'random':
            $n = (int)$_POST['random_n'];
            $cond .= " ORDER BY RAND() LIMIT " . ($n * $pools_num);
            break;
        default:
            throw new Exception();
    }

    $res = sql_pe("SELECT pool_name, revision FROM morph_annot_pools WHERE pool_id=? LIMIT 1", array($pool_id));
    $pool_info = $res[0];
    $lastrev = $pool_info['revision'];
    if (!$lastrev) {
        $r1 = sql_fetch_array(sql_query("SELECT MAX(rev_id) FROM tf_revisions"));
        $lastrev = $r1[0];
    }

    $matches = array();
    $next_pool_name = '';
    $next_pool_index = '';
    if (preg_match('/^(.+?)\s+#(\d+)/', $pool_info['pool_name'], $matches)) {
        $next_pool_name = $matches[1];
        $next_pool_index = $matches[2] + 1;
    } else {
        $next_pool_name = $pool_info['pool_name'];
        $next_pool_index = 2;
    }

    $time = time();

    sql_begin();
    $res = sql_pe("SELECT tf_id FROM morph_annot_candidate_samples $cond", array($pool_id));
    $i = 0;
    $tf_array = array();
    $promoted_pool_ids = array();
    foreach ($res as $r) {
        $tf_array[] = $r['tf_id'];
        if (++$i % $n == 0) {
            promote_samples_aux($tf_array, $pool_id, $lastrev, $next_pool_name, $next_pool_index, $promoted_pool_ids);
            $tf_array = array();
        }
    }
    if ($tf_array)
        promote_samples_aux($tf_array, $pool_id, $lastrev, $next_pool_name, $next_pool_index, $promoted_pool_ids);


    sql_query("UPDATE morph_annot_pools SET `status`=".MA_POOLS_STATUS_NOT_STARTED.", `revision`='$lastrev', `created_ts`='$time', `updated_ts`='$time' WHERE pool_id=$pool_id LIMIT 1");

    // delete tf_ids that were added
    sql_query("DELETE cs.* FROM morph_annot_candidate_samples cs LEFT JOIN morph_annot_samples s USING(tf_id) WHERE s.pool_id IN (".join(',', $promoted_pool_ids).")");

    if (isset($_POST['keep'])) {
        sql_pe(
            "INSERT INTO morph_annot_pools (SELECT NULL, pool_type, ".sql_quote($next_pool_name . ' #' . $next_pool_index).", token_check, users_needed, $time, $time, author_id, 0, ".MA_POOLS_STATUS_FOUND_CANDIDATES.", $lastrev FROM morph_annot_pools WHERE pool_id=? LIMIT 1)",
            array($pool_id)
        );
        sql_query("UPDATE morph_annot_candidate_samples SET pool_id=".sql_insert_id()." WHERE pool_id=$pool_id");
    }

    sql_pe("DELETE FROM morph_annot_candidate_samples WHERE pool_id=?", array($pool_id));
    sql_commit();
}
function publish_pool($pool_id) {
    if (!$pool_id)
        throw new UnexpectedValueException();

    $res = sql_pe("SELECT `status`, users_needed FROM morph_annot_pools WHERE pool_id=? LIMIT 1", array($pool_id));
    sql_begin();

    if ($res[0]['status'] < MA_POOLS_STATUS_IN_PROGRESS) {
        //all this should be done only if the pool is published for the 1st time
        $N = $res[0]['users_needed'];
        for ($i = 0; $i < $N; ++$i)
            sql_pe("INSERT INTO morph_annot_instances(SELECT NULL, sample_id, 0, 0, 0 FROM morph_annot_samples WHERE pool_id=? ORDER BY sample_id)", array($pool_id));
        sql_pe("INSERT INTO morph_annot_moderated_samples (SELECT sample_id, 0, 0, 0, 0, 0 FROM morph_annot_samples WHERE pool_id=? ORDER BY sample_id)", array($pool_id));
    }

    sql_pe("UPDATE morph_annot_pools SET `status`=".MA_POOLS_STATUS_IN_PROGRESS.", `updated_ts`=? WHERE pool_id=? LIMIT 1", array(time(), $pool_id));
    sql_commit();
}
function unpublish_pool($pool_id) {
    if (!$pool_id)
        throw new UnexpectedValueException();

    sql_pe("UPDATE morph_annot_pools SET `status`=".MA_POOLS_STATUS_ANSWERED.", `updated_ts`=? WHERE pool_id=? LIMIT 1", array(time(), $pool_id));
}
function moderate_pool($pool_id) {
    if (!$pool_id)
        throw new UnexpectedValueException();

    //we should only allow to moderate pools once we have all the answers
    $res = sql_pe("SELECT instance_id FROM morph_annot_instances WHERE answer=0 AND sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=?) LIMIT 1", array($pool_id));
    if (sizeof($res) > 0)
        throw new Exception("Пул заполнен не полностью");

    sql_pe("UPDATE morph_annot_pools SET `status`=".MA_POOLS_STATUS_MODERATION.", `updated_ts`=? WHERE pool_id=? LIMIT 1", array(time(), $pool_id));
}
function finish_moderate_pool($pool_id) {
    if (!$pool_id)
        throw new UnexpectedValueException();

    //only the pool moderator can finish moderation
    if (!check_moderator_right($_SESSION['user_id'], $pool_id))
        throw new Exception("Вы не модератор этого пула");

    //we cannot finish unless all the samples are moderated
    $res = sql_pe("SELECT sample_id FROM morph_annot_moderated_samples WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=?) AND answer = 0 LIMIT 1", array($pool_id));
    if (sizeof($res) > 0)
        throw new Exception("Не всё отмодерировано");

    // check for bad moderator answers
    $res = sql_pe("
        SELECT sample_id
        FROM morph_annot_moderated_samples
        JOIN morph_annot_samples
            USING (sample_id)
        WHERE pool_id=?
            AND answer = 99
            AND status IN (0, 1)
        LIMIT 1
    ", array($pool_id));
    if (sizeof($res) > 0)
        throw new Exception("Error in sample #".$r['sample_id']);

    sql_pe("UPDATE morph_annot_pools SET status=".MA_POOLS_STATUS_MODERATED.", updated_ts=? WHERE pool_id=? LIMIT 1", array(time(), $pool_id));
}
function begin_pool_merge($pool_id) {
    if (!user_has_permission("perm_merge"))
        throw new Exception("Недостаточно прав");
    if (!$pool_id)
        throw new UnexpectedValueException();

    // we can perform this only if pool has been moderated
    $res = sql_pe("SELECT status FROM morph_annot_pools WHERE pool_id=? LIMIT 1", array($pool_id));
    if ($res[0]['status'] != MA_POOLS_STATUS_MODERATED)
        throw new Exception("Пул не отмодерирован");

    sql_pe("UPDATE morph_annot_pools SET status=".MA_POOLS_STATUS_TO_MERGE.", updated_ts=? WHERE pool_id=? LIMIT 1", array(time(), $pool_id));
}
function get_available_tasks($user_id, $only_editable=false, $limit=0, $random=false) {
    global $config;
    $hot_types = array(0);
    if (isset($config['misc']['morph_annot_hot_pool_types']))
        $hot_types = explode(',', $config['misc']['morph_annot_hot_pool_types']);

    $tasks = array();

    if ($random)
        $order_string = "ORDER BY RAND()";
    else
        $order_string = "ORDER BY (pool_type in (".join(',', $hot_types).")) DESC, (complexity > 0) DESC, complexity, pool_type, created_ts";

    if ($limit)
        $limit_string = "LIMIT " . (2 * $limit);
    else
        $limit_string = "";

    $time = time();
    $cnt = 0;
    $pools = array();
    // memorize pool types with manual and complexity
    $types_with_manual = array();
    $type2complexity = array();
    $type_has_samples = array();
    $res = sql_query("SELECT `type_id`, `doc_link`, `complexity`
                , (SELECT COUNT(*) AS `pool_count` FROM `morph_annot_pools` AS `p` 
                        WHERE `p`.`pool_type` = `morph_annot_pool_types`.`type_id` AND `p`.`status` = ".MA_POOLS_STATUS_ARCHIVED.") AS `archieved_pools_count`
                FROM `morph_annot_pool_types`
                WHERE `doc_link` != '' OR `complexity` > 0");
    while ($r = sql_fetch_array($res)) {
        if ($r['doc_link'])
            $types_with_manual[] = $r['type_id'];
        if ($r['complexity'] > 0)
            $type2complexity[$r['type_id']] = $r['complexity'];
        if ($r['archieved_pools_count'] > 0)
            $type_has_samples[] = $r['type_id'];
    }
    // get all pools by status
    $res = sql_query("SELECT pool_id, pool_name, status, pool_type FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) WHERE status = ".MA_POOLS_STATUS_IN_PROGRESS." $order_string $limit_string");
    while ($r = sql_fetch_array($res)) {
        $pools[$r['pool_id']] = array('id' => $r['pool_id'], 'name' => $r['pool_name'], 'status' => $r['status'], 'num_started' => 0, 'num_done' => 0, 'num' => 0, 'group' => $r['pool_type']);
    }

    if (!$pools)
        return $tasks;

    $pool_ids = array_keys($pools);
    // get sample counts for selected pools
    // gather count of all available samples grouped by pool
    $rejected_or_owned = get_rejected_samples($user_id);

    $r_owned_samples = sql_query("
        SELECT sample_id
        FROM morph_annot_instances
        JOIN morph_annot_samples USING (sample_id)
        WHERE user_id = $user_id
        AND pool_id IN (" . implode(', ', $pool_ids) . ")
    ");
    while ($r = sql_fetch_array($r_owned_samples))
        $rejected_or_owned[] = $r['sample_id'];

    $r_available_samples = sql_query('
        SELECT pool_id,count(distinct sample_id) as cnt
        FROM morph_annot_instances
        LEFT JOIN morph_annot_samples USING(sample_id)
        WHERE
            answer=0
            AND ts_finish < ' . $time . '
            AND pool_id IN (' . implode(', ',$pool_ids) . ')
            AND sample_id NOT IN ('. join(', ', $rejected_or_owned).')
        GROUP BY pool_id');
    while ($available_samples = sql_fetch_array($r_available_samples)) {
        $pools[$available_samples['pool_id']]['num'] = $available_samples['cnt'];
    }
    // gather count of all samples with started instances with empty answer grouped by pool
    $r_started_samples = sql_query('
        SELECT pool_id, count(*) as cnt
        FROM morph_annot_instances
        LEFT JOIN morph_annot_samples USING(sample_id)
        WHERE user_id=' . $user_id . '
            AND morph_annot_instances.answer=0
            AND pool_id IN (' . implode(',',$pool_ids) . ')
        GROUP BY pool_id');
    while ($started_samples = sql_fetch_array($r_started_samples)) {
        $pools[$started_samples['pool_id']]['num_started'] = $started_samples['cnt'];
    }
    // gather count of all samples with instance & answer grouped by pool
    $r_done_samples = sql_query('
        SELECT pool_id, count(*) as cnt
        FROM morph_annot_instances
        LEFT JOIN morph_annot_samples USING(sample_id)
        WHERE user_id=' . $user_id . '
            AND morph_annot_instances.answer>0
            AND pool_id IN (' . implode(',', $pool_ids) . ')
        GROUP BY pool_id');
    while ($done_samples = sql_fetch_array($r_done_samples)) {
        $pools[$done_samples['pool_id']]['num_done'] = $done_samples['cnt'];
    }
    foreach ($pools as $pool) {
        if (
            // we are not interested in not available & not started pools
            $pool['num'] + $pool['num_started'] + $pool['num_done'] > 0 &&
            // we may be as well not interested in pools where nothing remains to do
            (!$only_editable || ($pool['num'] + $pool['num_started']) > 0)
        ) {
            if ($random)
                $tasks[] = $pool;
            else
                $tasks[$pool['group']]['pools'][] = $pool;

            ++$cnt;
            if ($limit > 0 && $cnt == $limit)
                break;
        }
    }

    if (!$random)
        foreach ($tasks as $group_id => $v) {
            $i = 0;
            while ($i < sizeof($v['pools'])) {
                if ($v['pools'][$i]['num'] + $v['pools'][$i]['num_started'] > 0) {
                    $tasks[$group_id]['first_id'] = $v['pools'][$i]['id'];
                    break;
                }
                ++$i;
            }
            if (isset($tasks[$group_id]['first_id']))
                while (true) {
                    $rand = mt_rand(0, sizeof($v['pools']) - 1);
                    if ($v['pools'][$rand]['num'] + $v['pools'][$rand]['num_started'] > 0) {
                        $tasks[$group_id]['random_id'] = $v['pools'][$rand]['id'];
                        break;
                    }
                }
            $tasks[$group_id]['has_manual'] = in_array($group_id, $types_with_manual);
            $tasks[$group_id]['complexity'] = isset($type2complexity[$group_id]) ? $type2complexity[$group_id] : 0;
            $tasks[$group_id]['has_samples'] = in_array($group_id, $type_has_samples);
            $tasks[$group_id]['name'] = preg_replace('/\s+#\d+\s*$/', '', $v['pools'][0]['name']);
            $tasks[$group_id]['is_hot'] = in_array($group_id, $hot_types);
        }

    return $tasks;
}
function get_my_answers($pool_id, $limit=10, $skip=0) {
    // TODO: we may certainly refactor here: this and get_annotation_packet() should share code
    $packet = array('my' => 1);
    $r = sql_fetch_array(sql_query("SELECT status, t.gram_descr FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) WHERE pool_id=$pool_id"));
    if ($r['status'] != MA_POOLS_STATUS_IN_PROGRESS)
        $packet['editable'] = 0;
    else
        $packet['editable'] = 1;
    $packet['gram_descr'] = explode('@', $r['gram_descr']);
    $user_id = $_SESSION['user_id'];

    $limit_str = '';
    if ($limit)
        $limit_str = " LIMIT $skip, $limit";
    $res = sql_query("SELECT instance_id, sample_id, answer FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND user_id=$user_id AND answer>0 $limit_str");
    if (!sql_num_rows($res)) return false;

    $gram_descr = array();
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT tf_id, rev_text FROM tf_revisions WHERE tf_id = (SELECT tf_id FROM morph_annot_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1) AND is_last=1 LIMIT 1"));
        $instance = get_context_for_word($r1['tf_id'], 4);
        $arr = xml2ary($r1['rev_text']);
        $parses = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
        $lemmata = array();
        foreach ($parses as $p) {
            $lemmata[] = $p['lemma_text'];
        }
        $instance['lemmata'] = implode(', ', array_unique($lemmata));
        $instance['id'] = $r['instance_id'];
        $instance['sample_id'] = $r['sample_id'];
        $instance['answer'] = $r['answer'];
        $packet['instances'][] = $instance;
    }
    return $packet;
}
function get_next_pool($user_id, $prev_pool_id) {
    if (!$user_id || !$prev_pool_id)
        throw new UnexpectedValueException();

    $time = time();
    $res = sql_query("SELECT pool_id FROM morph_annot_pools WHERE status = ".MA_POOLS_STATUS_IN_PROGRESS." AND pool_type = (SELECT pool_type FROM morph_annot_pools WHERE pool_id=$prev_pool_id LIMIT 1) ORDER BY created_ts");
    while ($r = sql_fetch_array($res)) {
        $res1 = sql_query("
            SELECT instance_id FROM morph_annot_instances LEFT JOIN morph_annot_samples USING (sample_id)
            WHERE answer = 0
            AND pool_id = ".$r['pool_id']."
            AND ts_finish < $time
            AND sample_id NOT IN (
                SELECT sample_id
                FROM morph_annot_instances
                WHERE user_id=$user_id)
            AND sample_id NOT IN (
                SELECT sample_id
                FROM morph_annot_rejected_samples
                WHERE user_id=$user_id)
            LIMIT 1
        ");
        if (sql_num_rows($res1) > 0)
            return $r['pool_id'];
    }
    return 0;
}
function get_rejected_samples($user_id, $pool_id=0) {
    $out = array(0);
    if ($pool_id > 0)
        $q = "
            SELECT sample_id
            FROM morph_annot_rejected_samples
            JOIN morph_annot_samples
                USING (sample_id)
            WHERE user_id=$user_id
            AND pool_id=$pool_id
        ";
    else
        $q = "
            SELECT sample_id
            FROM morph_annot_rejected_samples
            WHERE user_id=$user_id
        ";
    $res = sql_query($q);
    while ($r = sql_fetch_array($res))
        $out[] = $r['sample_id'];
    return $out;
}
function get_free_samples($user_id, $pool_id, $limit, $include_owned, $rejected=NULL) {
    if (!is_array($rejected))
        $rejected = get_rejected_samples($user_id, $pool_id);
    $time = time();
    return sql_query("
        SELECT instance_id, sample_id
        FROM morph_annot_instances
        JOIN morph_annot_samples
            USING (sample_id)
        WHERE pool_id = $pool_id
        AND sample_id NOT IN (
            SELECT DISTINCT sample_id
            FROM morph_annot_instances
            WHERE user_id=$user_id
        )
        AND sample_id NOT IN (".join(',', $rejected).")
        AND ".($include_owned ? "ts_finish=0" : "ts_finish < $time")."
        AND answer=0
        GROUP BY sample_id
        LIMIT $limit
    ");
}
function get_annotation_packet($pool_id, $size) {
    global $config;

    $r = sql_fetch_array(sql_query("SELECT status, t.gram_descr, revision, pool_type, doc_link FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) WHERE pool_id=$pool_id"));
    if ($r['status'] != MA_POOLS_STATUS_IN_PROGRESS)
        throw new Exception();
    $packet = array(
        'my' => 0,
        'editable' => 1,
        'pool_type' => $r['pool_type'],
        'has_manual' => (bool)$r['doc_link'],
        'gram_descr' => explode('@', $r['gram_descr'])
    );
    $user_id = $_SESSION['user_id'];
    $flag_new = 0;
    $pool_revision = $r['revision'];

    //if the user has something already reserved, let's start with that (but only if the poolid is the same!)
    $res = sql_query("SELECT instance_id, sample_id FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND user_id=$user_id AND answer=0 LIMIT $size");
    if (!sql_num_rows($res)) {
        //ok, we should find new samples
        //first, check non-owned ones
        $rejected = get_rejected_samples($user_id, $pool_id);
        $res = get_free_samples($user_id, $pool_id, $size, false, $rejected);
        $flag_new = 1;
        if (!sql_num_rows($res)) {
            //if nothing found, check owned but outdated ones
            $res = get_free_samples($user_id, $pool_id, $size, true, $rejected);
        }
    }
    if (!sql_num_rows($res)) return false;

    //when the timeout will be - same for each sample
    $ts_finish = time() +  $config['misc']['morph_annot_timeout'];
    if ($flag_new) sql_begin();
    $gram_descr = array();
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT tf_id, rev_text FROM tf_revisions WHERE tf_id = (SELECT tf_id FROM morph_annot_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1) AND rev_id <= $pool_revision ORDER BY rev_id DESC LIMIT 1"));
        $instance = get_context_for_word($r1['tf_id'], $config['misc']['morph_annot_user_context_size']);
        $arr = xml2ary($r1['rev_text']);
        $parses = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
        $lemmata = array();
        foreach ($parses as $p) {
            $lemmata[] = $p['lemma_text'];
        }
        $instance['lemmata'] = implode(', ', array_unique($lemmata));
        $instance['id'] = $r['instance_id'];
        $instance['sample_id'] = $r['sample_id'];
        $packet['instances'][] = $instance;
        if ($flag_new)
            sql_query("UPDATE morph_annot_instances SET user_id='$user_id', ts_finish='$ts_finish' WHERE instance_id= ".$r['instance_id']." LIMIT 1");
    }
    if ($flag_new) sql_commit();
    $packet['current_annotators'] = get_current_annotators($user_id);
    return $packet;
}
function get_current_annotators($exclude_id=0) {
    global $config;

    $time = time();
    $res = sql_query("
        SELECT user_shown_name
        FROM users
        WHERE user_id IN (
            SELECT DISTINCT user_id
            FROM morph_annot_click_log
            WHERE user_id != $exclude_id
            AND timestamp > $time - ".$config['misc']['morph_annot_current_annotators_threshold']."
        )
    ");
    $out = array(
        'count' => sql_num_rows($res),
    );
    if (sql_num_rows($res) > 0) {
        $r = sql_fetch_array($res);
        $out['random_name'] = $r[0];
    }

    return $out;
}
function update_annot_instance($id, $answer) {
    $user_id = $_SESSION['user_id'];
    if (!$id || !$answer || !$user_id)
        throw new UnexpectedValueException();

    // the pool should be editable
    $r = sql_fetch_array(sql_query("SELECT pool_id, `status` FROM morph_annot_pools WHERE pool_id = (SELECT pool_id FROM morph_annot_samples WHERE sample_id=(SELECT sample_id FROM morph_annot_instances WHERE instance_id=$id LIMIT 1) LIMIT 1)"));
    if ($r['status'] != MA_POOLS_STATUS_IN_PROGRESS)
        throw new Exception("Пул недоступен для разметки");

    $pool_id = $r['pool_id'];

    sql_begin();

    // does the instance really belong to this user?
    $r = sql_fetch_array(sql_query("SELECT user_id, answer FROM morph_annot_instances WHERE instance_id=$id LIMIT 1"));
    $previous_answer = $r['answer'] > 0;
    if ($r['user_id'] != $user_id) {
        // if another user has taken it, no chance
        if ($r['user_id'] > 0)
            throw new Exception();

        // or, perhaps, this user has rejected it before but has changed his mind
        $res = sql_query("SELECT sample_id FROM morph_annot_rejected_samples WHERE user_id=$user_id AND sample_id = (SELECT sample_id FROM morph_annot_instances WHERE instance_id=$id LIMIT 1) LIMIT 1");
        if (sql_num_rows($res) > 0) {
            $r = sql_fetch_array($res);
            sql_query("DELETE FROM morph_annot_rejected_samples WHERE user_id=$user_id AND sample_id = ".$r['sample_id']." LIMIT 1");
            sql_query("UPDATE morph_annot_instances SET user_id=$user_id, ts_finish=".(time() + 600)." WHERE instance_id=$id LIMIT 1");
        }
    }

    include_once('lib_awards.php');

    // a valid answer
    if ($answer > 0) {
        sql_query("UPDATE morph_annot_instances SET user_id=$user_id, answer='$answer' WHERE instance_id=$id LIMIT 1");
        update_user_rating($user_id, $pool_id, false, $previous_answer);
    }
    // or a rejected question
    elseif ($answer == -1) {
        sql_query("INSERT INTO morph_annot_rejected_samples (SELECT sample_id, $user_id FROM morph_annot_instances WHERE instance_id=$id LIMIT 1)");
        sql_query("UPDATE morph_annot_instances SET user_id='0', ts_finish='0', answer='0' WHERE instance_id=$id LIMIT 1");
        update_user_rating($user_id, $pool_id, true, $previous_answer);
    }
    sql_commit();
}
function check_moderator_right($user_id, $pool_id, $make_owner=false) {
    //the pool must have status=5 (under moderation) AND either have no moderator or have this user as moderator
    $res = sql_pe("SELECT `status`, moderator_id FROM morph_annot_pools WHERE pool_id = ? LIMIT 1", array($pool_id));
    if ($res[0]['status'] != MA_POOLS_STATUS_MODERATION)
        return false;
    if ($res[0]['moderator_id'] == 0) {
        if ($make_owner)
            sql_pe("UPDATE morph_annot_pools SET moderator_id=? WHERE pool_id=? LIMIT 1", array($user_id, $pool_id));
    } elseif ($res[0]['moderator_id'] != $user_id)
        return false;
    return true;
}
function moder_agree_with_all($pool_id) {
    $samples = get_morph_samples_page($pool_id, true, 1, 0, 'not_moderated');
    sql_begin();
    foreach ($samples['samples'] as $sample) {
        if ($sample['disagreed'] === 0)
            save_moderated_answer($sample['id'], $sample['instances'][0]['answer_num'], 0);
    }
    sql_commit();
}
function save_moderated_answer($id, $answer, $manual, $field_name='answer') {
    $user_id = $_SESSION['user_id'];
    if (!$id || !$user_id || $answer < 0)
        throw new UnexpectedValueException();
    $r = sql_fetch_array(sql_query("SELECT pool_id FROM morph_annot_samples WHERE sample_id = $id LIMIT 1"));
    $pool_id = $r['pool_id'];

    sql_begin();
    if (!check_moderator_right($user_id, $pool_id, true))
        throw new Exception("Вы не модератор этого пула");

    sql_query("UPDATE morph_annot_moderated_samples SET user_id=$user_id, `$field_name`=$answer, `manual`=$manual WHERE sample_id=$id LIMIT 1");
    sql_commit();
    if ($field_name != 'answer')
        return 1;
    //check whether it was the last sample to be moderated
    $res = sql_query("
        SELECT sample_id
        FROM morph_annot_moderated_samples
        LEFT JOIN morph_annot_samples USING (sample_id)
        WHERE pool_id=$pool_id
        AND answer = 0
        LIMIT 1
    ");
    if (sql_num_rows($res) == 0)
        return 2;
    return 1;
}
function save_moderated_status($id, $status) {
    return save_moderated_answer($id, $status, 1, 'status');
}
function get_sample_comments($sample_id) {
    $res = sql_query("SELECT comment_id, user_shown_name AS user_name, timestamp, text FROM morph_annot_comments LEFT JOIN users USING(user_id) WHERE sample_id=$sample_id ORDER BY timestamp");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[] = array(
            'id' => $r['comment_id'],
            'author' => $r['user_name'],
            'timestamp' => $r['timestamp'],
            'text' => $r['text']
        );
    }
    return $out;
}
function log_click($sample_id, $type) {
    if (!is_logged())
        throw new Exception();
    $user_id = $_SESSION['user_id'];
    if (!$sample_id || !$type || !$user_id)
        throw new UnexpectedValueException();

    if ($type == -1) $type = 77;

    $ts = time();
    sql_pe("INSERT INTO morph_annot_click_log VALUES(?, ?, ?, ?)", array($sample_id, $user_id, $ts, $type));
}
function count_all_answers() {
    $res = sql_query("SELECT COUNT(*) FROM morph_annot_instances WHERE answer > 0");
    $r = sql_fetch_array($res);
    return $r[0];
}
function get_pool_manual_page($type_id) {
    $res = sql_pe("SELECT doc_link FROM morph_annot_pool_types WHERE type_id=? LIMIT 1", array($type_id));
    return $res[0]['doc_link'];
}
function get_search_results($query, $exact_form=true) {
    $forms = array($query);
    if (!$exact_form) {
        include_once('lib_dict.php');
        $forms = get_all_forms_by_lemma_text($query);
    }
    $r = sql_fetch_array(sql_query("
        SELECT COUNT(*)
        FROM form2tf
        WHERE form_text IN (".join(",", array_map('sql_quote', $forms)).")
    "));

    $out = array('total' => $r[0], 'results' => array());
    $res = sql_query("
        SELECT tf_id
        FROM form2tf
        WHERE form_text IN (".join(",", array_map('sql_quote', $forms)).")
        LIMIT 100
    ");
    while ($r = sql_fetch_array($res))
        $out['results'][] = get_context_for_word($r['tf_id'], 20);
    return $out;
}
?>
