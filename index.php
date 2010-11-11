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
        case 'publications':
            $smarty->display('publications.tpl');
            break;
        case 'stats':
            $smarty->assign('stats', get_common_stats());
            $smarty->display('stats.tpl');
            break;
        default:
            $smarty->display('index.tpl');
    }
}
else
    $smarty->display('index.tpl');
?>
