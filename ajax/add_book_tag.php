<?php
require_once('../lib/header.php');
require_once('../lib/lib_books.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.(int)books_add_tag((int)$_GET['book_id'], mysql_real_escape_string($_GET['tag_name'])).'"/>';
?>
