<?php
require('lib/header.php');
require('lib/lib_dict.php');
$action = $_GET['act'];
if (is_admin()) {
    switch($action) {
        case 'add':
            $book_id = array_pop($_POST['book']);
            if (!addtext_add($_POST['txt'], (int)$book_id, (int)$_POST['newpar'])) {
                die("Text adding failed");
            } else {
                header("Location:add.php");
            }
            break;
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
    print $config['msg_notadmin'];
}
