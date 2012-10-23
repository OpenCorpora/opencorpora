<?php
require('lib/header.php');
require_once('lib/lib_annot.php');
if (isset($_GET['rand'])) {
    $r = sql_fetch_array(sql_query("SELECT sent_id FROM sentences ORDER BY RAND() LIMIT 1", 0));
    header("Location:sentence.php?id=".$r['sent_id']);
    return;
}

if (isset($_GET['page'])) {
    $page = $_GET['page'];
    switch ($page) {
        case 'about':
        case 'team':
        case 'publications':
            $smarty->assign('active_page','about');
            $smarty->display('static/' . $page . '.tpl');
            break;
        case 'downloads':
            $smarty->assign('active_page','downloads');
            $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $smarty->setCacheLifetime(600);
            if (!is_cached('static/downloads.tpl')) {
                $smarty->assign('dl', get_downloads_info());
                $smarty->assign('stats', get_common_stats());
            }
            $smarty->display('static/downloads.tpl');
            break;
        case 'top100':
            $smarty->assign('stats', get_top100_info($_GET['what'], $_GET['type']));
            $smarty->display('top100.tpl');
            break;
        case 'stats':
            $uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $smarty->assign('active_page','stats');
            $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $smarty->setCacheLifetime(300);
            if (!is_cached('stats.tpl', $uid)) {
                $smarty->assign('stats', get_common_stats());
                $smarty->assign('ma_count', count_all_answers());
            }
            $smarty->display('stats.tpl', $uid);
            break;
        case 'tag_stats':
            $smarty->assign('active_page','stats');
            $smarty->assign('stats', get_tag_stats());
            $smarty->display('tag_stats.tpl');
            break;
        case 'export':
            $smarty->assign('active_page','downloads');
            $smarty->display('static/doc/export.tpl');
            break;
        case 'faq':
            $smarty->assign('active_page','about');
            $smarty->assign('content', get_wiki_page('FAQ'));
            $smarty->assign('title', 'FAQ');
            $smarty->display('static/faq.tpl');
            break;
        default:
            header("Location:index.php");
            break;
    }
}
else {
    if (!is_admin()) {
        if (is_logged())
            $smarty->assign('available', get_available_tasks($_SESSION['user_id'], true, 5, true));
        $smarty->assign('answer_count', count_all_answers());
    }
    $smarty->display('index.tpl');
}
?>
