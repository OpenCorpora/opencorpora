<?php
require('lib/header.php');
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
            if (!$smarty->isCached('static/downloads.tpl')) {
                $smarty->assign('dl', get_downloads_info());
            }
            $smarty->display('static/downloads.tpl');
            break;
        case 'top100':
            $smarty->assign('stats', get_ngram_top100_info($_GET['type']));
            $smarty->display('top100.tpl');
            break;
        case 'stats':
            $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $smarty->setCacheLifetime(300);
            if (!$smarty->isCached('stats.tpl')) {
                $smarty->assign('stats', get_common_stats());
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
            $smarty->display('index.tpl');
    }
}
else
    $smarty->display('index.tpl');
?>
