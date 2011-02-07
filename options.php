<?php
require('lib/header.php');
if (is_logged()) {
    if (isset($_GET['act'])) {
        $action = $_GET['act'];
    } else
        $action = '';
    switch($action) {
        case 'save':
            save_user_options($_POST);
            break;
        case 'readonly_on':
            if (is_admin()) {
                set_readonly_on();
                header('Location:options.php');
                return;
            } else
                show_error($config['msg_notadmin']);
            break;
        case 'readonly_off':
            if (is_admin()) {
                set_readonly_off();
                header('Location:options.php');
                return;
            } else
                show_error($config['msg_notadmin']);
            break;
        default:
            $smarty->assign('meta', get_meta_options());
            $smarty->assign('current_email', get_user_email($_SESSION['user_id']));
            show_page('options.tpl');
    }
} else
    show_error($config['msg_notlogged']);
?>
