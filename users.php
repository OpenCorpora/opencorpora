<?php
require('lib/header.php');
require_once('lib/lib_users.php');
if (is_admin()) {
    $action = isset($_GET['act']) ? $_GET['act'] : '';
    switch ($action) {
        case 'save':
            save_users($_POST);
            header("Location:users.php");
            break;
        default:
            $smarty->assign('users', get_users_page());
            $smarty->display('users_admin.tpl');
    }
} else {
    show_error($config['msg']['notadmin']);
}
log_timing();
?>
