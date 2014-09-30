<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');
if (!user_has_permission('perm_dict')) {
    return false;
}

$res = true;
try {
    switch ($_GET['act']) {
        case 'forget':
            forget_pending_token($_GET['token_id'], $_GET['rev_id']);
            break;
        case 'update':
            update_pending_token($_GET['token_id'], $_GET['rev_id']);
            break;
        default:
            $res = false;
    }
}
catch (Exception $e) {
    $res = false;
}

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.(int)$res.'"/>';
log_timing(true);
?>
