<?php
require_once('lib/header.php');
require_once('lib/lib_ne.php');
require_once('lib/lib_users.php');

// TODO: permissions?

$smarty->assign('active_page', 'tasks');

check_logged();

$action = isset($_GET['act']) ? $_GET['act'] : '';
$tagset_id = get_current_tagset();

switch ($action) {

    case 'manual':
        $smarty->assign('content', get_wiki_page("nermanual/" . (int)$_GET['id']));
        $smarty->display('static/doc/annotation.tpl');
        break;

    default:
        $is_ner_mod = user_has_permission(PERM_NE_MODER);

        $smarty->assign('possible_guidelines',
            get_ne_guidelines());  // TODO read from db
        $smarty->assign('is_ner_mod', $is_ner_mod);
        $smarty->assign('current_guideline', $tagset_id);
        $smarty->assign('page', get_books_with_NE($tagset_id, !$is_ner_mod));
        $smarty->display(($is_ner_mod ? 'ner/main-moderator.tpl' : 'ner/main.tpl'));
}
log_timing();
