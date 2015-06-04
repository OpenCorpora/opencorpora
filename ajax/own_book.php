<?php
require_once('../lib/header_ajax.php');

try {
    if (!user_has_permission(PERM_ADDER))
        throw new Exception("Недостаточно прав");

    if (!isset($_POST['sid']) || !isset($_POST['status']))
        throw new UnexpectedValueException();

    $sid = $_POST['sid'];
    $status = $_POST['status'];

    if ($status == 0) {
        $res = sql_pe("SELECT user_id FROM sources WHERE source_id=? LIMIT 1", array($sid));
        if ($res[0]['user_id'] != $_SESSION['user_id'])
            throw new Exception("Book is already owned");
    }

    $user_id = $status > 0 ? $_SESSION['user_id'] : 0;

    sql_pe("UPDATE sources SET user_id=? WHERE source_id=? LIMIT 1", array($user_id, $sid));
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
