<?php
header('Content-type: text/xml; charset=utf-8');
require_once('../lib/header.php');
require_once('../lib/lib_books.php');
$id = (int)$_GET['id'];
$out = books_get_select($id);
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response>'.$out.'</response>';
?>
