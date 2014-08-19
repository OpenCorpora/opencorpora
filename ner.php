<?php
require_once('lib/header.php');
require_once('lib/lib_ne.php');

// TODO: permissions?
/*if (!user_has_permission('perm_syntax')) {
    show_error($config['msg']['notadmin']);
    return;
}*/

$action = isset($_GET['act']) ? $_GET['act'] : '';

switch ($action) {

    case 'manual':
        $smarty->assign('content', get_wiki_page("Инструкция по определению именованных сущностей"));
        $smarty->display('static/doc/annotation.tpl');
        break;

    default:

        $smarty->assign('page', get_books_with_NE());
        $smarty->display('ner/main.tpl');
}
