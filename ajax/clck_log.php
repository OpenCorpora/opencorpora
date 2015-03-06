<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_annot.php');

try {
    if (!isset($_POST['id']) || !isset($_POST['type']))
        throw new UnexpectedValueException();
    log_click($_POST['id'], $_POST['type']);
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
