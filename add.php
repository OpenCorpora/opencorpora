<?php
require('lib/header.php');
require('lib/lib_tokenizer.php');
$action = GET('act', '');

switch ($action) {
    case 'add':
        $book_id = array_pop(POST('book'));
        addtext_add(POST('source_text'), POST('sentence'), (int)$book_id, (int)POST('newpar'));
        header("Location:books.php?book_id=$book_id");
        break;
    case 'check':
        $smarty->assign('check', addtext_check(POST('txt'), POST('book_id', 0)));
        $smarty->display('addtext_check.tpl');
        break;
    default:
        check_permission(PERM_ADDER);
        $smarty->assign('txt', POST('txt', ''));
        $smarty->display('addtext.tpl');
}

log_timing();
