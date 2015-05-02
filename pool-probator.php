<?php
require('lib/header.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_annot.php');

if (!isset($_GET['pool_type']))
    throw new UnexpectedValueException('Wrong pool_type');

$pool_size = 5;

if ($t = get_proba_packet((int)$_GET['pool_type'], $pool_size)) {
    $smarty->assign('packet', $t);
    $smarty->display('qa/morph_annot_proba.tpl');
}

function get_proba_packet($pool_type, $size) {
    global $config;

    $res = sql_query("SELECT `pool_type`.`doc_link`, `pool_type`.`gram_descr`, COUNT(*) AS `pool_count`
                        FROM `morph_annot_pools` AS `pool`
                        INNER JOIN `morph_annot_pool_types` AS `pool_type` ON `pool`.`pool_type` = `pool_type`.`type_id`
                        WHERE `pool`.`pool_type` = $pool_type AND `pool`.`status` = ".MA_POOLS_STATUS_ARCHIVED." ");
    $r = sql_fetch_array($res);    
    if ((int)$r['pool_count'] == 0)
        throw new UnexpectedValueException("No archieved pools for pool_type=$pool_type");

    $packet = array(
        'pool_type' => $pool_type,
        'has_manual' => (bool)$r['doc_link'],
        'gram_descr' => explode('@', $r['gram_descr'])
    );
    
    $res = sql_query("SELECT `sample`.`sample_id`, `sample`.`pool_id`, `pool`.`revision`, `answer`.`answer` 
                FROM `morph_annot_samples` AS `sample`
                INNER JOIN `morph_annot_moderated_samples` AS `answer` ON `sample`.`sample_id` = `answer`.`sample_id`
                INNER JOIN `morph_annot_pools` AS `pool` ON `pool`.`pool_id` = `sample`.`pool_id`
                WHERE `pool`.`pool_type` = $pool_type AND `pool`.`status` = ".MA_POOLS_STATUS_ARCHIVED."
                ORDER BY RAND()
                LIMIT $size");

    if (!sql_num_rows($res))
        throw new Exception("No samples for pool_type=$pool_type");

    $gram_descr = array();
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT tf_id, rev_text FROM tf_revisions WHERE tf_id = (SELECT tf_id FROM morph_annot_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1) AND rev_id <= ".$r['revision']." ORDER BY rev_id DESC LIMIT 1"));
        $instance = get_context_for_word($r1['tf_id'], $config['misc']['morph_annot_user_context_size']);
        $arr = xml2ary($r1['rev_text']);
        $parses = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
        $lemmata = array();
        foreach ($parses as $p) {
            $lemmata[] = $p['lemma_text'];
        }
        $instance['lemmata'] = implode(', ', array_unique($lemmata));
        $instance['sample_id'] = $r['sample_id'];
        $instance['correct_answer'] = $r['answer'];
        $packet['instances'][] = $instance;
    }
    return $packet;
}
log_timing();
?>
