<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');

$result['ids'] = array();

$res = sql_pe("SELECT lemma_id FROM dict_lemmata WHERE lemma_text=? AND deleted=0", array($_POST['q']));
foreach ($res as $r) {
    $result['ids'][] = $r['lemma_id'];
}
log_timing(true);
die(json_encode($result));
?>
