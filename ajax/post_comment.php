<?php
require_once('../lib/header.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if (
    !isset($_POST['type']) ||
    !isset($_POST['id']) ||
    !isset($_POST['text']) ||
    !isset($_SESSION['user_id'])
) {
    echo '<response ok="0"/>';
    return;
}

$id = (int)$_POST['id'];
$text = mysql_real_escape_string($_POST['text']);
$reply_to = isset($_POST['reply_to']) ? (int)$_POST['reply_to'] : 0;
$user_id = $_SESSION['user_id'];

$time = time();

switch($_POST['type']) {
    case 'sentence':
        $q = "INSERT INTO sentence_comments VALUES(NULL, '$reply_to', '$id', '$user_id', '$text', '$time')";
        break;
    case 'source':
        $q = "INSERT INTO sources_comments VALUES(NULL, '$id', '$user_id', '$text', '$time')";
        break;
    case 'morph_annot':
        $q = "INSERT INTO morph_annot_comments VALUES(NULL, '$id', '$user_id', '$text', '$time')";
        break;
    default:
        echo '<response ok="0"/>';
        return;
}

if (sql_query($q)) {
    echo '<response ok="1" ts="'.date("d.m.y, H:i", $time).'" id="'.sql_insert_id().'"/>';
} else {
    echo '<response ok="0"/>';
}
?>
