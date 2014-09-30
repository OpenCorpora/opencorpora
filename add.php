<?php
require('lib/header.php');
require('lib/lib_tokenizer.php');
if (isset($_GET['act']))
    $action = $_GET['act'];
else $action = '';
if (user_has_permission('perm_adder')) {
    switch ($action) {
        case 'add':
            $book_id = array_pop($_POST['book']);
            addtext_add($_POST['source_text'], $_POST['sentence'], (int)$book_id, (int)$_POST['newpar']);
            header("Location:books.php?book_id=$book_id");
            break;
        case 'check':
            $smarty->assign('check', addtext_check($_POST));
            $smarty->display('addtext_check.tpl');
            break;
        default:
            if (isset($_POST['txt'])) {
                $smarty->assign('txt', $_POST['txt']);
            }
            $smarty->display('addtext.tpl');
    }
} else {
    show_error($config['msg']['notadmin']);
}
log_timing();
