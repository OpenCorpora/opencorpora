<?php
require_once('lib/header.php');
require_once('lib/lib_ne.php');

// TODO: permissions?

check_logged();

$action = isset($_GET['act']) ? $_GET['act'] : '';
$tagset_id = 1; // TODO switch

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
