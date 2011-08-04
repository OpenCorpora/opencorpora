<?php
header('Content-type: text/html; charset=utf-8');
require_once('../lib/header.php');

$substring = mysql_real_escape_string($_GET['q']);
$res = sql_query("SELECT DISTINCT tag_name FROM book_tags WHERE tag_name LIKE '$substring%' ORDER BY tag_name LIMIT 10");
while($line = sql_fetch_assoc($res)) {
   echo $line['tag_name']."\n";
}
?>
