<?php
require('lib/header.php');
require('lib/lib_books.php');
$action = isset($_GET['act']) ? $_GET['act'] : '';
if (!$action) {
    if (isset($_GET['book_id']) && $book_id = (int)$_GET['book_id']) {
        $smarty->assign('book', get_book_page($book_id, isset($_GET['full'])));
        $smarty->display('book.tpl');
    } else {
        $smarty->assign('books', get_books_list());
        $smarty->display('books.tpl');
    }
}
elseif (is_admin() && $action == 'del_sentence') {
    $sid = (int)$_GET['sid'];
    if (!delete_sentence($sid)) {
        show_error();
    } else {
        header("Location:books.php?book_id=".(int)$_GET['book_id'].'&full');
    }
}
elseif (user_has_permission('perm_adder')) {
    switch($action) {
        case 'add':
            $book_name = mysql_real_escape_string($_POST['book_name']);
            $book_parent = (int)$_POST['book_parent'];
            if (books_add($book_name, $book_parent)) {
                if (isset($_POST['goto']))
                    header("Location:books.php?book_id=".sql_insert_id());
                else
                    header("Location:books.php?book_id=$parent_id");
            } else
                show_error();
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
            if (books_add_tag($book_id, $tag_name)) {
                header("Location:books.php?book_id=$book_id");
            } else {
                die("Couldn't add tag");
            }
            break;
        case 'del_tag':
            $book_id = (int)$_GET['book_id'];
            $tag_name = mysql_real_escape_string($_GET['tag_name']);
            books_del_tag($book_id, $tag_name);
            break;
        case 'merge_sentences':
            merge_sentences((int)$_POST['id1'], (int)$_POST['id2']);
            break;
        case 'split_token':
            split_token((int)$_POST['tid'], (int)$_POST['nc']);
            break;
        case 'split_sentence':
            if($a = split_sentence((int)$_POST['tid']))
                header("Location:books.php?book_id=".$a[0]."&full#sen".$a[1]);
            else
                show_error();
            break;
        case 'split_paragraph':
            split_paragraph((int)$_GET['sid']);
            break;
    }
} else {
    show_error($config['msg']['notadmin']);
}
?>
