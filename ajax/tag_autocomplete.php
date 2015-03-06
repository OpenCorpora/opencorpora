<?php
require_once('../lib/header_ajax.php');

$res = sql_pe("SELECT DISTINCT tag_name FROM book_tags WHERE tag_name LIKE ? ORDER BY tag_name LIMIT 10", array($_GET['query'].'%'));
$result['suggestions'] = array();
foreach ($res as $line) {
   $result['suggestions'][] = $line['tag_name'];
}
log_timing(true);
die(json_encode($result));
?>
