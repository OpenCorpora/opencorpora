<?php
require_once('../lib/header_ajax.php');
header('Content-type: application/json');

$res = sql_pe("SELECT DISTINCT tag_name FROM book_tags WHERE tag_name LIKE ? ORDER BY tag_name LIMIT 10", array($_GET['query'].'%'));
$out = array('suggestions' => array());
foreach ($res as $line) {
   $out['suggestions'][] = $line['tag_name'];
}
log_timing(true);
die(json_encode($out));
?>
