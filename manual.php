<?php
require('lib/header.php');
require_once('lib/lib_morph_pools.php');

$pool_type = GET('pool_type', 0);
$what = GET('what', '');

switch ($what) {
    case 'morph_moderation':
        $smarty->assign('content', get_wiki_page("Инструкция для модераторов"));
        break;
    case 'newslist_announce':
        $smarty->assign('content', get_wiki_page("Newslist opencorpora-dev"));
        break;
    default:
        if ($pool_type)
            $smarty->assign('content', get_wiki_page(get_pool_manual_page($pool_type)));
        else
            $smarty->assign('content', get_wiki_page("Инструкция по интерфейсу для снятия омонимии"));
}

$smarty->display('static/doc/annotation.tpl');
log_timing();
?>
