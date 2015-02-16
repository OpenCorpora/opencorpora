<?php
require_once('../lib/header_ajax.php');
header('Content-type: application/json');

$res = sql_pe("SELECT DISTINCT dl.lemma_id, dl.lemma_text, SUBSTR(grammems, 7, 4) AS gr FROM form2lemma fl LEFT JOIN dict_lemmata dl ON (fl.lemma_id=dl.lemma_id) WHERE dl.deleted=0 AND fl.form_text like ? order by dl.lemma_text limit 10", array($_GET['query'] . '%'));
$out = array('suggestions' => array());
foreach ($res as $line) {
    $out['suggestions'][] = array(
        'value' => $line['lemma_text'],
        'data' => array(
            'id' => $line['lemma_id'],
            'gram' => $line['gr']
        )
    );
}
log_timing(true);
die(json_encode($out));
?>
