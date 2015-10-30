<?php
require_once('lib/header.php');
require_once('lib/lib_ne.php');

// TODO: permissions?
/*if (!user_has_permission(PERM_SYNTAX)) {
    show_error($config['msg']['notadmin']);
    return;
}*/
if (!is_logged()) {
    show_error($config['msg']['notlogged']);
    return;
}

$action = isset($_GET['act']) ? $_GET['act'] : '';
$tagset_id = get_current_tagset();

switch ($action) {

    case 'manual':
        $smarty->assign('content', get_wiki_page("Инструкция по определению именованных сущностей"));
        $smarty->display('static/doc/annotation.tpl');
        break;

    default:

        $smarty->assign('page', get_books_with_NE($tagset_id));
        $smarty->display('ner/main.tpl');
}
log_timing();
