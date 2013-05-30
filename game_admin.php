<?php
require('lib/header.php');
require_once('lib/lib_awards.php');

if (!is_admin()) {
    show_error($config['msg']['notadmin']);
    exit;
}

$action = isset($_GET['act']) ? $_GET['act'] : '';
switch ($action) {
    default:
        $smarty->assign('badges', get_badges_info());
        $smarty->display('game_admin.tpl');
}
?>
