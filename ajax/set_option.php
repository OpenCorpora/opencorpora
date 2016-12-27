<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_users.php');

try {
    check_logged();
    save_user_option(POST('option'), POST('value'));
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
