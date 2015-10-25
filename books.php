<?php
require('lib/header.php');
require_once('lib/lib_books.php');
require_once('lib/lib_anaphora_syntax.php');
require_once('lib/lib_ne.php');

$action = isset($_GET['act']) ? $_GET['act'] : '';
if (!$action) {
    if (isset($_GET['book_id']) && $book_id = $_GET['book_id']) {
        $smarty->assign('book', get_book_page($book_id, isset($_GET['full'])));
        $smarty->display('book.tpl');
    } else {
        $smarty->assign('books', get_books_list());
        $smarty->display('books.tpl');
    }
}
elseif (is_admin() && in_array($action, array('del_sentence', 'move'))) {
    switch ($action) {
        case 'del_sentence':
            delete_sentence($_GET['sid']);
            header("Location:books.php?book_id=".$_GET['book_id'].'&full');
            break;
        case 'move':
            books_move($_POST['book_id'], $_POST['book_to']);
            header("Location:books.php?book_id=$book_to");
            break;
    }
}
elseif (user_has_permission(PERM_SYNTAX) && $action == 'anaphora') {
    if (isset($_GET['book_id']) && $book_id = $_GET['book_id']) {
        $book = get_book_page($book_id, TRUE);
        $groups = array();
        $smarty->assign('group_types', get_syntax_group_types());

        foreach ($book['paragraphs'] as &$paragraph) {

            foreach ($paragraph['sentences'] as &$sentence) {
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

elseif  (/*user_has_permission(PERM_SYNTAX) && */is_logged() && $action == 'ner') {
    if (isset($_GET['book_id']) && $book_id = $_GET['book_id']) {

        $tagset_id = 1; // TODO switch

        $book = get_book_page($book_id, TRUE);
        $paragraphs_status = get_ne_paragraph_status($book_id, $_SESSION['user_id'], $tagset_id);

        foreach ($book['paragraphs'] as &$paragraph) {
            $ne = get_ne_by_paragraph($paragraph['id'], $_SESSION['user_id'], $tagset_id);
            $paragraph['named_entities'] = isset($ne['entities']) ? $ne['entities'] : array();
            $paragraph['annotation_id'] = isset($ne['annot_id']) ? $ne['annot_id'] : array();
            $paragraph['ne_by_token'] = get_ne_tokens_by_paragraph($paragraph['id'], $_SESSION['user_id'], $tagset_id);
            $paragraph['comments'] = get_comments_by_paragraph($paragraph['id'], $_SESSION['user_id'], $tagset_id);

            $paragraph['mine'] = false;
            if (in_array($paragraph['id'], $paragraphs_status['unavailable']) or
                in_array($paragraph['id'], $paragraphs_status['done_by_user'])) {
                $paragraph['disabled'] = true;
            }
            elseif (in_array($paragraph['id'], $paragraphs_status['started_by_user'])) {
                $paragraph['mine'] = true;
            }
        }

        $smarty->assign('book', $book);
        $smarty->assign('types', get_ne_types($tagset_id));
        $smarty->assign('use_fast_mode', $_SESSION['options'][5]);
        $smarty->display('ner/book.tpl');
    } else {
        throw new UnexpectedValueException();
    }
}

elseif (user_has_permission(PERM_ADDER)) {
    switch ($action) {
        case 'add':
            $book_name = trim($_POST['book_name']);
            $book_parent = $_POST['book_parent'];
            $book_id = books_add($book_name, $book_parent);
            if (isset($_POST['goto']))
                header("Location:books.php?book_id=$book_id");
            else
                header("Location:books.php?book_id=$book_parent");
        case 'rename':
            $name = trim($_POST['new_name']);
            $book_id = $_POST['book_id'];
            books_rename($book_id, $name);
            header("Location:books.php?book_id=$book_id");
            break;
        case 'add_tag':
            $book_id = $_POST['book_id'];
            $tag_name = $_POST['tag_name'];
            books_add_tag($book_id, $tag_name);
            header("Location:books.php?book_id=$book_id");
            break;
        case 'del_tag':
            $book_id = $_GET['book_id'];
            $tag_name = $_GET['tag_name'];
            books_del_tag($book_id, $tag_name);
            header("Location:books.php?book_id=$book_id");
            break;
        case 'merge_sentences':
            $sent1 = $_POST['id1'];
            $sent2 = $_POST['id2'];
            merge_sentences($sent1, $sent2);
            header("Location:sentence.php?id=$sent1");
            break;
        case 'split_token':
            $val = split_token($_POST['tid'], $_POST['nc']);
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
        case 'merge_paragraph':
            list($book_id, $sent_id) = merge_paragraphs($_GET['pid']);
            header("Location:books.php?book_id=$book_id&full#sen$sent_id");
            break;
    }
} else {
    show_error($config['msg']['notadmin']);
}
log_timing();
?>
