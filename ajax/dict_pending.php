<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');

if (!user_has_permission(PERM_DICT)) {
    return false;
}

try {
    switch ($_POST['act']) {
        case 'forget':
            forget_pending_token($_POST['token_id'], $_POST['rev_id']);
            break;
        case 'update':
            update_pending_token($_POST['token_id'], $_POST['rev_id']);
            break;
        default:
            $result['error'] = 1;
    }
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
?>
