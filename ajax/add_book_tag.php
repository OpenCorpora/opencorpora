<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');
$result = 1;

try {
    books_add_tag((int)$_GET['book_id'], mysql_real_escape_string($_GET['tag_name']));
}
catch (Exception $e) {
    $result = 0;
}

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.$result.'"/>';
?>
