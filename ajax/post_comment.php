<?php
require_once('../lib/header.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if (!isset($_POST['sent_id']) || !isset($_POST['text']) || !isset($_SESSION['user_id'])) {
    echo '<response ok="0"/>';
    return;
}

$sent_id = (int)$_POST['sent_id'];
$text = $_POST['text'];
$reply_to = isset($_POST['reply_to']) ? (int)$_POST['reply_to'] : 0;

$time = time();
if (sql_query("INSERT INTO sentence_comments VALUES(NULL, '$reply_to', '$sent_id', '".$_SESSION['user_id']."', '".mysql_real_escape_string($text)."', '$time')")) {
    echo '<response ok="1" ts="'.date("d.m.y, H:i", $time).'" id="'.sql_insert_id().'"/>';
} else {
    echo '<response ok="0"/>';
}
?>
