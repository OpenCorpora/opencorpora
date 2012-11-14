<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');
if (!user_has_permission('perm_dict')) {
    return false;
}

switch ($_GET['act']) {
    case 'forget':
        $res = forget_pending_token((int)$_GET['token_id'], (int)$_GET['rev_id']);
        break;
    case 'update':
        $res = update_pending_token((int)$_GET['token_id'], (int)$_GET['rev_id']);
        break;
    default:
        return false;
}

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.(int)$res.'"/>';
?>
