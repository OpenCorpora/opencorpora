<?php
require('lib/header.php');
require_once('lib/lib_qa.php');
if (isset($_GET['rand'])) {
    $r = sql_fetch_array(sql_query("SELECT sent_id FROM sentences ORDER BY RAND() LIMIT 1", 0));
    header("Location:sentence.php?id=".$r['sent_id']);
    return;
}

if (isset($_GET['page'])) {
    $page = $_GET['page'];
    switch($page) {
        case 'about':
            $smarty->display('static/about.tpl');
            break;
        case 'team':
            $smarty->display('static/team.tpl');
            break;
        case 'publications':
            $smarty->display('static/publications.tpl');
            break;
        case 'downloads':
            $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $smarty->setCacheLifetime(600);
            if (!is_cached('static/downloads.tpl')) {
                $smarty->assign('dl', get_downloads_info());
            }
            $smarty->display('static/downloads.tpl');
            break;
        case 'top100':
            $smarty->assign('stats', get_top100_info($_GET['what'], $_GET['type']));
            $smarty->display('top100.tpl');
            break;
        case 'stats':
            $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $smarty->setCacheLifetime(300);
            if (!is_cached('stats.tpl')) {
                $smarty->assign('stats', get_common_stats());
                $smarty->assign('ma_count', count_all_answers());
            }
            $smarty->display('stats.tpl');
            break;
        case 'tag_stats':
            $smarty->assign('stats', get_tag_stats());
            $smarty->display('tag_stats.tpl');
            break;
        case 'export':
            $smarty->display('static/doc/export.tpl');
            break;
        default:
            if (is_logged()) {
                $smarty->assign('available', get_available_tasks($_SESSION['user_id'], true));
            }
            $smarty->display('index.tpl');
    }
}
else {
    if (is_logged() && !is_admin()) {
        $smarty->assign('available', get_available_tasks($_SESSION['user_id'], true, 5));
        $smarty->assign('answer_count', count_all_answers());
    }
    $smarty->display('index.tpl');
}
?>
