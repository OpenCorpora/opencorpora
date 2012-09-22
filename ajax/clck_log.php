<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_annot.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.(int)log_click((int)$_GET['id'], (int)$_GET['type']).'"/>';
