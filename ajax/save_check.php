<?php
require_once('../lib/header_ajax.php');
$type = $_POST['type'];
$id = (int)$_POST['id'];
$value = $_POST['value'];

try {
    switch($type) {
        case 'token':
            if (!user_has_permission('perm_check_tokens'))
                throw new Exception();
            sql_begin();
            sql_query("DELETE FROM sentence_check WHERE sent_id=$id AND user_id=".$_SESSION['user_id']." AND `status`=1 LIMIT 1");
            if ($value === 'true') {
                sql_query("INSERT INTO sentence_check VALUES('$id', '".$_SESSION['user_id']."', '1', '".time()."')");
                sql_commit();
            }
            break;
        case 'source':
            if (!user_has_permission('perm_adder'))
                throw new Exception();
            if ($value === '0' || $value === '1')
                sql_query("INSERT INTO sources_status VALUES('$id', '".$_SESSION['user_id']."', '$value', '".time()."')");
            break;
        default:
            throw new Exception();
    }
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
