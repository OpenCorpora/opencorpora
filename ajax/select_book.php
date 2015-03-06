<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');

$id = (int)$_POST['id'];
$t = get_books_for_select($id);

$result['books'] = array();
foreach ($t as $id => $title) {
    $result['books'][] = array('id' => $id, 'title' => $title);
}

log_timing(true);
die(json_encode($result));
?>
