<?php
require_once('../lib/header_ajax.php');
$type = $_GET['type'];
$id = (int)$_GET['id'];
$value = $_GET['value'];
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response>';

$result = 1;
try {
    switch($type) {
        case 'token':
            if (!user_has_permission('perm_check_tokens'))
                throw new Exception();
            sql_begin();
            sql_query_pdo("DELETE FROM sentence_check WHERE sent_id=$id AND user_id=".$_SESSION['user_id']." AND `status`=1 LIMIT 1");
            if ($value === 'true') {
                sql_query_pdo("INSERT INTO sentence_check VALUES('$id', '".$_SESSION['user_id']."', '1', '".time()."')");
                sql_commit();
            }
            break;
        case 'source':
            if (!user_has_permission('perm_adder'))
                throw new Exception();
            if ($value === '0' || $value === '1')
                sql_query_pdo("INSERT INTO sources_status VALUES('$id', '".$_SESSION['user_id']."', '$value', '".time()."')");
        default:
            throw new Exception();
    }
}
catch (Exception $e) {
    $result = 0;
}
echo '<result ok="'.$result.'"/></response>';
?>
