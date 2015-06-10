<?php

require_once('../lib/header_ajax.php');

if (!is_logged()) {
    return;
}

try {
    switch ($_POST['act']) {
        case 'approve':
        // POST contains id and value (true for checked/false for unchecked)
            $result['error'] = 0;
            break;
        case 'comment':
        // POST contains id and text
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