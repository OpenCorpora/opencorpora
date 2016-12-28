<?php
require_once('../lib/header_ajax.php');

try {

    $id = POST('id');
    $text = POST('text');
    if (!$id || !$text || !isset($_SESSION['user_id']))
        throw new UnexpectedValueException();

    $reply_to = POST('reply_to', 0);
    $user_id = $_SESSION['user_id'];

    $time = time();

    switch (POST('type')) {
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
    $result['timestamp'] = date("d.m.y, H:i", $time);
    $result['id'] = sql_insert_id();
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
