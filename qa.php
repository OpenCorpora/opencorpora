<?php
require('lib/header.php');
require('lib/lib_qa.php');

$action = '';
if (isset($_GET['act']))
    $action = $_GET['act'];

switch($action) {
    case 'tokenizer':
        if (user_has_permission('perm_adder')) {
            $smarty->assign('obj', get_page_tok_strange());
            $smarty->display('qa/tokenizer.tpl');
        } else {
            show_error($config['msg_notadmin']);
        }
        break;
    case 'empty_books':
        if (user_has_permission('perm_adder')) {
            $smarty->assign('books', get_empty_books());
            $smarty->display('qa/empty_books.tpl');
        } else {
            show_error($config['msg_notadmin']);
        }
        break;
    default:
        header("Location:index.php");
}

?>
