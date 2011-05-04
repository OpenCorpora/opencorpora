<?php
require('lib/header.php');
require('lib/lib_books.php');
$action = isset($_GET['act']) ? $_GET['act'] : '';
if (user_has_permission('perm_adder')) {
    switch($action) {
        case 'add':
            $book_name = mysql_real_escape_string($_POST['book_name']);
            $book_parent = (int)$_POST['book_parent'];
            books_add($book_name, $book_parent);
            break;
        case 'rename':
            $name = mysql_real_escape_string($_POST['new_name']);
            $book_id = (int)$_POST['book_id'];
            books_rename($book_id, $name);
            break;
        case 'move':
            $book_id = (int)$_POST['book_id'];
            $book_to = (int)$_POST['book_to'];
            books_move($book_id, $book_to);
            break;
        case 'add_tag':
            $book_id = (int)$_POST['book_id'];
            $tag_name = mysql_real_escape_string($_POST['tag_name']);
            books_add_tag($book_id, $tag_name);
            break;
        case 'del_tag':
            $book_id = (int)$_GET['book_id'];
            $tag_name = mysql_real_escape_string($_GET['tag_name']);
            books_del_tag($book_id, $tag_name);
            break;
        case 'merge_sentences':
            merge_sentences((int)$_POST['id1'], (int)$_POST['id2']);
            break;
        case 'merge_tokens':
            merge_tokens((int)$_POST['id1'], (int)$_POST['id2']);
            break;
        default:
            if (isset($_GET['book_id']) && $book_id = (int)$_GET['book_id']) {
                $smarty->assign('book', get_book_page($book_id, isset($_GET['ext']), isset($_GET['full'])));
                $smarty->display('book.tpl');
            } else {
                $smarty->assign('books', get_books_list());
                $smarty->display('books.tpl');
            }
    }
} else {
    show_error($config['msg_notadmin']);
}
?>
