<?php
require_once('../lib/header_ajax.php');
header('Content-type: text/html; charset=utf-8');

$res = sql_pe("SELECT DISTINCT dl.lemma_id, dl.lemma_text, SUBSTR(grammems, 7, 4) AS gr FROM form2lemma fl LEFT JOIN dict_lemmata dl ON (fl.lemma_id=dl.lemma_id) WHERE fl.form_text like ? order by dl.lemma_text limit 10", array($_GET['q'] . '%'));
foreach ($res as $line) {
   echo $line['lemma_text'] . '|' . $line['gr'] . '|' . $line['lemma_id'] ."\n";
}
log_timing(true);
?>
