<?php
require('lib/header.php');
require('lib/lib_dict.php');
if (isset($_GET['act']))
    $action = $_GET['act'];
else $action = '';
if (is_admin()) {
    switch($action) {
        case 'add':
            $book_id = array_pop($_POST['book']);
            if (!addtext_add($_POST['txt'], $_POST['sentence'], (int)$book_id, (int)$_POST['newpar'])) {
                show_error("Text adding failed");
            } else {
                header("Location:books.php?book_id=$book_id");
            }
            return;
        case 'check':
            $smarty->assign('check', addtext_check($_POST['txt']));
            $smarty->display('addtext_check.tpl');
            break;
        default:
            if (isset($_POST['txt'])) {
                $smarty->assign('txt', $_POST['txt']);
            }
            $smarty->display('addtext.tpl');
    }
} else {
    show_error($config['msg_notadmin']);
}
