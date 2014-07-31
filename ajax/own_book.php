<?php
require_once('../lib/header_ajax.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

try {
    if (!user_has_permission('perm_adder'))
        throw new Exception("Недостаточно прав");

    if (!isset($_GET['sid']) || !isset($_GET['status']))
        throw new UnexpectedValueException();

    $sid = $_GET['sid'];
    $status = $_GET['status'];

    if ($status == 0) {
        $res = sql_pe("SELECT user_id FROM sources WHERE source_id=? LIMIT 1", array($sid));
        if ($res[0]['user_id'] != $_SESSION['user_id'])
            throw new Exception("Book is already owned");
    }

    $user_id = $status > 0 ? $_SESSION['user_id'] : 0;

    sql_pe("UPDATE sources SET user_id=? WHERE source_id=? LIMIT 1", array($user_id, $sid));
    echo '<result ok="1"/>';
}
catch (Exception $e) {
    echo '<result ok="0"/>';
}
