<?php
session_start();
$var = $_GET['var'];
$value = $_GET['value'];
$_SESSION[$var] = $value;
print 1;
?>
