<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');
$id = (int)$_GET['id'];
$t = get_books_for_select($id);
$out = '';
foreach ($t as $id => $title) {
    $out .= "<option value='$id'>$title</option>";
}

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response>'.$out.'</response>';
log_timing(true);
?>
