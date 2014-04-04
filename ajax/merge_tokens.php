<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

try {
    if (!user_has_permission('perm_adder'))
        throw new Exception("Недостаточно прав");
    if (!isset($_GET['ids']))
        throw new UnexpectedValueException();

    merge_tokens_ii(explode(',', $_GET['ids']));
    echo '<result ok="1"/>';
}
catch (Exception $e) {
    echo '<result ok="0"/>';
}
?>
