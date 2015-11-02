<?php

require_once('../lib/header_ajax.php');
require_once('../lib/lib_qa.php');

try {
    switch ($_POST['act']) {
        case 'approve':
            save_merge_fail_status($_POST['id'], $_POST['value']);
            $result['error'] = 0;
            break;
        case 'comment':
            save_merge_fail_comment($_POST['id'], $_POST['text']);
            $result['error'] = 0;
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
