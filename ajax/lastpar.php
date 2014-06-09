<?php
require_once('../lib/header_ajax.php');
$book_id = (int)$_GET['book_id'];
$r = sql_fetch_array(sql_query_pdo("SELECT `par_id`, `pos` FROM `paragraphs` WHERE `book_id`=$book_id ORDER BY `pos` DESC LIMIT 1", 0));
$num = $r['pos'];
$par_id = $r['par_id'];
$txt = '';

$r = sql_fetch_array(sql_query_pdo("SELECT SUBSTRING_INDEX(source, ' ', 5) AS `start` FROM sentences WHERE `par_id` = $par_id ORDER BY `pos` LIMIT 1"));
$txt = $r['start'];

if ($txt) $txt .= ' <...> ';

$r = sql_fetch_array(sql_query_pdo("SELECT SUBSTRING_INDEX(source, ' ', -5) AS `end` FROM sentences WHERE `par_id` = $par_id ORDER BY `pos` DESC LIMIT 1"));
$txt .= $r['end'];

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response num="'.$num.'">'.htmlspecialchars($txt).'</response>';
?>
