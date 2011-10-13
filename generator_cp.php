<?php

require_once('lib/header.php');
require_once('lib/lib_generator.php');

if(is_admin()) {
    $action = isset($_GET['act']) ? $_GET['act'] : '';
    switch($action) {
        case 'run':
            $update = run_generator($_POST['tag']);
            $smarty->assign('success', $update['success']);
            $smarty->assign('output',  $update['output']);

            $current = get_generator_status();
            $smarty->assign('status', $current['status']);
            $smarty->assign('since', $current['since']);

            $smarty->display('generator_cp.tpl');
            break;

        case 'toggle':
            $new = toggle_generator_status();
            $smarty->assign('status', $new['status']);
            $smarty->assign('since', $new['since']);
            $smarty->display('generator_cp.tpl');

        default:
            $current = get_generator_status();
            $smarty->assign('status', $current['status']);
            $smarty->assign('since', $current['since']);
            $smarty->display('generator_cp.tpl');
    }
}
else {
    show_error($config['msg']['notadmin']);
}
?>
