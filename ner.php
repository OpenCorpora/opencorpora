<?php
require_once('lib/header.php');
require_once('lib/lib_ne.php');

// TODO: permissions?

check_logged();

$action = isset($_GET['act']) ? $_GET['act'] : '';
$tagset_id = get_current_tagset();

switch ($action) {

    case 'manual':
        $smarty->assign('content', get_wiki_page("nermanual/" . (int)$_GET['id']));
        $smarty->display('static/doc/annotation.tpl');
        break;

    default:
        $smarty->assign('possible_guidelines',
            array(1 => "Default (2014)", 2 => "Dialogue Eval (2016)"));
        $smarty->assign('current_guideline', $tagset_id);
        $smarty->assign('page', get_books_with_NE($tagset_id));
        $smarty->display('ner/main.tpl');
}
log_timing();
