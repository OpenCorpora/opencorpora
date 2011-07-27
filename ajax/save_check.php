<?php
header('Content-type: text/xml; charset=utf-8');
require_once('../lib/header.php');
$type = $_GET['type'];
$id = (int)$_GET['id'];
$value = $_GET['value'];
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response>';

switch($type) {
    case 'token':
        if (user_has_permission('perm_check_tokens')) {
            if (sql_query("DELETE FROM sentence_check WHERE sent_id=$id AND user_id=".$_SESSION['user_id']." AND `status`=1 LIMIT 1")) {
                if ($value === 'true') {
                    if (sql_query("INSERT INTO sentence_check VALUES('$id', '".$_SESSION['user_id']."', '1', '".time()."')")) {
                        echo '<result ok="1"/></response>';
                        return;
                    }
                } else {
                    echo '<result ok="1"/></response>';
                    return;
                }
            }
        }
    case 'source':
        if (user_has_permission('perm_adder')) {
            if ($value === '0' || $value === '1') {
                if (sql_query("INSERT INTO sources_status VALUES('$id', '".$_SESSION['user_id']."', '$value', '".time()."')")) {
                    echo '<result ok="1"/></response>';
                    return;
                }
            }
        }
}
echo '<result ok="0"/></response>';
?>
