<?php
header('Content-type: text/xml; charset=utf-8');
require_once('../lib/header.php');
$book_id = (int)$_GET['book_id'];
$r = sql_fetch_array(sql_query("SELECT `par_id`, `pos` FROM `paragraphs` WHERE `book_id`=$book_id ORDER BY `pos` DESC LIMIT 1", 0));
$num = $r['pos'];
$par = $r['par_id'];
$txt = '';

if ($res = sql_query("SELECT `tf_text` AS txt FROM `text_forms` WHERE `sent_id` = (SELECT `sent_id` FROM `sentences` WHERE `par_id` = $par_id ORDER BY `pos` LIMIT 1) ORDER BY `pos` LIMIT 5", 0)) {
    while($r = sql_fetch_array($res)) {
        $txt .= $r['txt'].' ';
    }
}
if ($txt) $txt .= '<...>';
if ($res = sql_query("SELECT `tf_text` AS txt FROM `text_forms` WHERE `sent_id` = (SELECT `sent_id` FROM `sentences` WHERE `par_id` = $par_id ORDER BY `pos` DESC LIMIT 1) ORDER BY `pos` DESC LIMIT 5", 0)) {
    while($r = sql_fetch_array($res)) {
        $txt .= $r['txt'].' ';
    }
}

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response num="'.$num.'">'.htmlspecialchars($txt).'</response>';
?>
