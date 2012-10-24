<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_annot.php');

if (isset($_GET['id']) && isset($_GET['type']))
    $result = (int)log_click((int)$_GET['id'], (int)$_GET['type']);
else
    $result = 0;

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.$result.'"/>';
