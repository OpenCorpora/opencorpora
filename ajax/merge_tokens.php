<?php
require_once('../lib/header.php');
require_once('../lib/lib_books.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
if (!user_has_permission('perm_adder') || !isset($_GET['ids'])) {
    echo '<result ok="0"/>';
    return;
}
if (merge_tokens_ii(explode(',', $_GET['ids']))) {
    echo '<result ok="1"/>';
} else {
    echo '<result ok="0"/>';
}
?>
