<?php
require_once('../lib/header.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if ((!isset($_POST['sent_id']) && !isset($_POST['sid'])) || !isset($_POST['text']) || !isset($_SESSION['user_id'])) {
    echo '<response ok="0"/>';
    return;
}

$sid = isset($_POST['sent_id']) ? (int)$_POST['sent_id']: (int)$_POST['sid'];
$text = $_POST['text'];
$reply_to = isset($_POST['reply_to']) ? (int)$_POST['reply_to'] : 0;

$time = time();

if (isset($_POST['sent_id'])) {
    //this is a comment for a sentence
    $q = "INSERT INTO sentence_comments VALUES(NULL, '$reply_to', '$sid', '".$_SESSION['user_id']."', '".mysql_real_escape_string($text)."', '$time')";
} else {
    //this is a comment for a text source
    $q = "INSERT INTO sources_comments VALUES(NULL, '$sid', '".$_SESSION['user_id']."', '".mysql_real_escape_string($text)."', '$time')";
}

if (sql_query($q)) {
    echo '<response ok="1" ts="'.date("d.m.y, H:i", $time).'" id="'.sql_insert_id().'"/>';
} else {
    echo '<response ok="0"/>';
}
?>
