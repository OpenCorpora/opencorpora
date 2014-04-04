<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_annot.php');

$result = 1;
try {
    if (!isset($_GET['id']) || !isset($_GET['type']))
        throw new UnexpectedValueException();
    log_click((int)$_GET['id'], (int)$_GET['type']);
}
catch (Exception $e) {
    $result = 0;
}

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.$result.'"/>';
