<?php
require('lib/header.php');
require('lib/lib_books.php');
require('lib/lib_syntax.php');
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
    delete_sentence($sid);
    header("Location:books.php?book_id=".(int)$_GET['book_id'].'&full');
}
elseif (user_has_permission('perm_syntax') && $action == 'anaphora') {
    if (isset($_GET['book_id']) && $book_id = (int)$_GET['book_id']) {
        $book = get_book_page($book_id, TRUE);
        $groups = array();
        $smarty->assign('group_types', get_syntax_group_types());

        foreach ($book['paragraphs'] as &$paragraph) {

            foreach ($paragraph as &$sentence) {
                $sentence['props'] = get_pronouns_by_sentence($sentence['id']);
                foreach ($sentence['tokens'] as &$token) {
                    $groups[$token['id']] = $token['groups']
                        = get_moderated_groups_by_token($token['id']);
                }
            }
        }

        $smarty->assign('anaphora', get_anaphora_by_book($book_id));
        $smarty->assign('book', $book);
        $smarty->assign('token_groups', $groups);
        $smarty->display('syntax/book.tpl');
    } else {
        throw new UnexpectedValueException();
    }
}
elseif (user_has_permission('perm_adder')) {
    switch ($action) {
        case 'add':
            $book_name = mysql_real_escape_string(trim($_POST['book_name']));
            $book_parent = (int)$_POST['book_parent'];
            $book_id = books_add($book_name, $book_parent);
            if (isset($_POST['goto']))
                header("Location:books.php?book_id=$book_id");
            else
                header("Location:books.php?book_id=$book_parent");
        case 'rename':
            $name = mysql_real_escape_string(trim($_POST['new_name']));
            $book_id = (int)$_POST['book_id'];
            books_rename($book_id, $name);
            header("Location:books.php?book_id=$book_id");
            break;
        case 'move':
            $book_id = (int)$_POST['book_id'];
            $book_to = (int)$_POST['book_to'];
            books_move($book_id, $book_to);
            header("Location:books.php?book_id=$book_to");
            break;
        case 'add_tag':
            $book_id = (int)$_POST['book_id'];
            $tag_name = mysql_real_escape_string($_POST['tag_name']);
            books_add_tag($book_id, $tag_name);
            header("Location:books.php?book_id=$book_id");
            break;
        case 'del_tag':
            $book_id = (int)$_GET['book_id'];
            $tag_name = mysql_real_escape_string($_GET['tag_name']);
            books_del_tag($book_id, $tag_name);
            header("Location:books.php?book_id=$book_id");
            break;
        case 'merge_sentences':
            $sent1 = (int)$_POST['id1'];
            $sent2 = (int)$_POST['id2'];
            merge_sentences($sent1, $sent2);
            header("Location:sentence.php?id=$sent1");
            break;
        case 'split_token':
            $val = split_token((int)$_POST['tid'], (int)$_POST['nc']);
            header("Location:books.php?book_id=".$val[0]."&full#sen".$val[1]);
            break;
        case 'split_sentence':
            $a = split_sentence($_POST['tid']);
            header("Location:books.php?book_id=".$a[0]."&full#sen".$a[1]);
            break;
        case 'split_paragraph':
            $sent_id = $_GET['sid'];
            $book_id = split_paragraph($sent_id);
            header("Location:books.php?book_id=$book_id&full#sen$sent_id");
            break;
    }
} else {
    show_error($config['msg']['notadmin']);
}
?>
