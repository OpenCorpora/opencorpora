<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');

try {
    books_add_tag(POST('book_id'), POST('tag_name'));
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
?>
