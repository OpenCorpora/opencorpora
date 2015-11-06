<?php
require('lib/header.php');
require_once('lib/lib_morph_pools.php');
require_once('lib/lib_stats.php');
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
            $smarty->assign('active_page', 'downloads');
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
            $weekly = isset($_GET['weekly']);
            $smarty->assign('active_page', 'stats');
            $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $smarty->setCacheLifetime(300);
            $cache_key = $uid.'@'.(int)$weekly.(isset($_GET['team']) ? (int)$_GET['team'] : 0);
            if (!is_cached('stats.tpl', $cache_key)) {
                $smarty->assign('user_stats', get_user_stats($weekly, isset($_GET['team']) ? $_GET['team'] : 0));
                $smarty->assign('ma_count', count_all_answers());
            }
            $smarty->display('stats.tpl', $cache_key);
            break;
        case 'tag_stats':
            $smarty->assign('active_page', 'stats');
            $smarty->assign('stats', get_tag_stats());
            $smarty->display('tag_stats.tpl');
            break;
        case 'genre_stats':
            $smarty->assign('active_page', 'stats');
            $smarty->assign('stats', get_common_stats());
            $smarty->display('genre_stats.tpl');
            break;
        case 'charts':
            $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $smarty->setCacheLifetime(300);
            if (!is_cached('charts.tpl')) {
                $smarty->assign('words_chart', get_word_stats_for_chart());
                $smarty->assign('ambig_chart', get_ambiguity_stats_for_chart());
                $smarty->assign('pools_stats', get_pools_stats());
                $smarty->assign('annot_chart', get_annot_stats_for_chart());
            }
            $smarty->display('charts.tpl');
            break;
        case 'pool_charts':
            $smarty->assign('main', get_extended_pools_stats());
            $smarty->assign('moder', get_moderation_stats());
            $smarty->display('ext_charts.tpl');
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
        case 'achievements':
            $am = new AchievementsManager($_SESSION['user_id']);
            $smarty->assign('stats', $am->pull_stats());
            $smarty->assign('manager', $am);
            $smarty->display('achievements/all_achievements.tpl');
            break;
        case 'achievement':
            if (!empty($_GET['uid']) && !empty($_GET['type'])) {
                $uid = (int)$_GET['uid'];
                $type = $_GET['type'];

                $am = new AchievementsManager($uid);
                $smarty->assign('user', get_user_info($uid));
                $smarty->assign('user_id', $uid);

                $all = $am->pull_all();

                if (!empty($all[$type]) && $all[$type]->given) {

                    $smarty->assign('achievement', $all[$type]);
                    $smarty->display('achievements/one_achievement.tpl');
                    break;
                }
            }
        default:
            header("Location:index.php");
            break;
    }
}
else {
    if (!is_admin()) {
        if (is_logged())
            $smarty->assign('available', get_available_tasks($_SESSION['user_id'], true, $config['misc']['mainpage_tasks_limit'], true));
        $smarty->assign('answer_count', count_all_answers());
    }
    $smarty->display('index.tpl');
}
log_timing();
?>
