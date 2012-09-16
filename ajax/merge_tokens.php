<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');
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
