<?php
require_once('../lib/header.php');
require_once('../lib/lib_annot.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.(int)log_click((int)$_GET['id'], (int)$_GET['type']).'"/>';
