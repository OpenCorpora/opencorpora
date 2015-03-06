<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');

try {
    if (!user_has_permission('perm_adder'))
        throw new Exception("Недостаточно прав");
    if (!isset($_POST['ids']))
        throw new UnexpectedValueException();

    merge_tokens_ii(explode(',', $_POST['ids']));
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
