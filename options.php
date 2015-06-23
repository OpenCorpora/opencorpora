<?php
require('lib/header.php');
require_once('lib/lib_achievements.php');

if (is_logged()) {
    if (isset($_GET['act'])) {
        $action = $_GET['act'];
    } else
        $action = '';
    switch ($action) {
        case 'save':
            save_user_options($_POST);
            alert_set('success','Настройки сохранены');
            header('Location:options.php');
            break;
        case 'save_team':
            save_user_team($_POST['team_id'], $_POST['new_team_name']);
            if ($_POST['team_id'] || $_POST['new_team_name']) {
                $am = new AchievementsManager((int)$_SESSION['user_id']);
                $am->emit(EventTypes::JOINED_TEAM);
            }
            alert_set('success','Настройки сохранены');
            header('Location:options.php');
            break;
        case 'readonly_on':
            if (is_admin()) {
                set_readonly_on();
                header('Location:options.php');
                return;
            } else
                show_error($config['msg']['notadmin']);
            break;
        case 'readonly_off':
            if (is_admin()) {
                set_readonly_off();
                header('Location:options.php');
                return;
            } else
                show_error($config['msg']['notadmin']);
            break;
        default:
            $mgr = new UserOptionsManager();
            $smarty->assign('meta', $mgr->get_all_options(true));
            $smarty->assign('current_email', get_user_email($_SESSION['user_id']));
            $smarty->assign('current_name', get_user_shown_name($_SESSION['user_id']));
            $smarty->assign('teams',get_team_list());
            $smarty->assign('user_team',get_user_team($_SESSION['user_id']));
            $smarty->display('options.tpl');
    }
} else
    show_error($config['msg']['notlogged']);
log_timing();
