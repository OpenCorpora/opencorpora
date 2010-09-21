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
        default:
            $smarty->display('options.tpl');
    }
} else
    show_error($config['msg_notlogged']);
?>
