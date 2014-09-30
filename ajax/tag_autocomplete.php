<?php
require_once('../lib/header_ajax.php');
header('Content-type: text/html; charset=utf-8');

$res = sql_pe("SELECT DISTINCT tag_name FROM book_tags WHERE tag_name LIKE ? ORDER BY tag_name LIMIT 10", array($_GET['q'].'%'));
foreach ($res as $line) {
   echo $line['tag_name']."\n";
}
log_timing(true);
?>
