<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');

try {
    if (!is_admin())
        throw new Exception();
    save_token_text(POST('token_id'), POST('token_text'));
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
