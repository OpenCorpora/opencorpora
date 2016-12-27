<?php

require_once('lib/header.php');
require_once('lib/lib_generator.php');

$action = GET('act', '');

$current = get_generator_status();
$smarty->assign('status', $current['status']);
$smarty->assign('since', $current['since']);
$smarty->assign('tag', $current['tag']);
$smarty->assign('next', $current['next']);

switch ($action) {
    case 'toggle':
        $new = toggle_generator_status();
        $smarty->assign('status', $new['status']);
        $smarty->assign('since', $new['since']);
        $smarty->assign('tag', $new['tag']);
        $smarty->assign('next', $new['next']);

        break;
}

$smarty->display('generator_cp.tpl');
log_timing();
?>
