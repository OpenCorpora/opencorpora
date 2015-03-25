<?php
require_once('../lib/header_ajax.php');
$result = array('readonly' => file_exists($config['project']['readonly_flag']) ? 1 : 0);
die(json_encode($result));
?>
