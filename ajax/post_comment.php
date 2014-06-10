<?php
require_once('../lib/header_ajax.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

try {
    if (
        !isset($_POST['type']) ||
        !isset($_POST['id']) ||
        !$_POST['id'] ||
        !isset($_POST['text']) ||
        !isset($_SESSION['user_id'])
    )
        throw new UnexpectedValueException();

    $id = $_POST['id'];
    $text = trim($_POST['text']);
    $reply_to = isset($_POST['reply_to']) ? $_POST['reply_to'] : 0;
    $user_id = $_SESSION['user_id'];

    $time = time();

    switch($_POST['type']) {
        case 'sentence':
            sql_pe("INSERT INTO sentence_comments VALUES(NULL, ?, ?, ?, ?, ?)", array($reply_to, $id, $user_id, $text, $time));
            break;
        case 'source':
            sql_pe("INSERT INTO sources_comments VALUES(NULL, ?, ?, ?, ?)", array($id, $user_id, $text, $time));
            break;
        case 'morph_annot':
            sql_pe("INSERT INTO morph_annot_comments VALUES(NULL, ?, ?, ?, ?)", array($id, $user_id, $text, $time));
            break;
        default:
            throw new UnexpectedValueException();
    }

    sql_query($q);
    echo '<response ok="1" ts="'.date("d.m.y, H:i", $time).'" id="'.sql_insert_id().'"/>';
}
catch (Exception $e) {
    echo '<response ok="0"/>';
}
?>
