<?php
require_once('../lib/header_ajax.php');

$book_id = (int)$_POST['book_id'];
$r = sql_fetch_array(sql_query("SELECT `par_id`, `pos` FROM `paragraphs` WHERE `book_id`=$book_id ORDER BY `pos` DESC LIMIT 1", 0));
$num = $r['pos'];
$par_id = $r['par_id'] ?: 0;

$result['text'] = '';

$r = sql_fetch_array(sql_query("SELECT SUBSTRING_INDEX(source, ' ', 5) AS `start` FROM sentences WHERE `par_id` = $par_id ORDER BY `pos` LIMIT 1"));
$result['text'] = $r['start'];

if ($result['text']) $result['text'] .= ' <...> ';

$r = sql_fetch_array(sql_query("SELECT SUBSTRING_INDEX(source, ' ', -5) AS `end` FROM sentences WHERE `par_id` = $par_id ORDER BY `pos` DESC LIMIT 1"));
$result['text'] .= $r['end'];

$result['text'] = htmlspecialchars($result['text']);
$result['num'] = $num;

log_timing(true);
die(json_encode($result));
?>
