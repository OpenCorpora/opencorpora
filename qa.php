<?php
require('lib/header.php');
require('lib/lib_qa.php');

$action = '';
if (isset($_GET['act']))
    $action = $_GET['act'];

switch($action) {
    case 'sent_split':
        if (user_has_permission('perm_adder')) {
            $smarty->assign('sentences', get_page_sent_strange());
            $smarty->display('qa/sent_split.tpl');
        } else {
            show_error($config['msg']['notadmin']);
        }
        break;
    case 'tokenizer':
        if (user_has_permission('perm_adder')) {
            $smarty->assign('obj', get_page_tok_strange(isset($_GET['newest'])));
            $smarty->display('qa/tokenizer.tpl');
        } else {
            show_error($config['msg']['notadmin']);
        }
        break;
    case 'empty_books':
        if (user_has_permission('perm_adder')) {
            $smarty->assign('books', get_empty_books());
            $smarty->display('qa/empty_books.tpl');
        } else {
            show_error($config['msg']['notadmin']);
        }
        break;
    case 'dl_urls':
        if (user_has_permission('perm_adder')) {
            $smarty->assign('urls', get_downloaded_urls());
            $smarty->display('qa/dl_urls.tpl');
        } else {
            show_error($config['msg']['notadmin']);
        }
        break;
    case 'book_tags':
        if (user_has_permission('perm_adder')) {
            $smarty->assign('errata', get_tag_errors());
            $smarty->display('qa/book_tags.tpl');
        }
    default:
        header("Location:index.php");
}

?>
