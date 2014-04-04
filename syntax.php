<?php
require_once('lib/header.php');
require_once('lib/lib_syntax.php');

if (!user_has_permission('perm_syntax')) {
    show_error($config['msg']['notadmin']);
    return;
}

$action = isset($_GET['act']) ? $_GET['act'] : '';

switch ($action) {
    case 'finish_moder':
        finish_syntax_moderation((int)$_GET['book_id']);
        header("Location:syntax.php");
        break;
    case 'set_status':
        set_syntax_annot_status((int)$_GET['book_id'], (int)$_GET['status']);
        header("Location:syntax.php");
        break;
    case 'set_moderated':
        become_syntax_moderator((int)$_GET['book_id']);
        header("Location:syntax.php");
        break;

    default:
        $smarty->assign('page', get_books_with_syntax());
        $smarty->display('syntax/main.tpl');
}
?>
