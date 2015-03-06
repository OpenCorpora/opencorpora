<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');

try {
    books_add_tag($_POST['book_id'], $_POST['tag_name']);
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
?>
