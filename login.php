<?php
require('lib/header.php');

$action = GET('act', '');

if (isset($_SESSION['user_id']) && in_array($action, array('', 'login', 'login_openid', 'register'))) {
    header("Location:index.php");
    return;
}

switch($action) {
    case 'login':
        if (user_login(POST('login'), POST('passwd'))) {
            if (isset($_SESSION['return_to']))
                header('Location:'.$_SESSION['return_to']);
            else
                header('Location:index.php');
        } else {
            header('Location:login.php?act=error');
        }
        log_timing();
        exit();
    case 'login_openid':
        $r = user_login_openid(POST('token'));
        switch ($r) {
            case 1:
                if (isset($_SESSION['return_to']))
                    header('Location:'.$_SESSION['return_to']);
                else
                    header('Location:index.php');
                break;
            case 2:
                $smarty->display('openid_license.tpl');
                break;
            default:
                header('Location:login.php?act=error');
        }
        log_timing();
        exit();
    case 'login_openid2':
        user_login_openid_agree(POST('agree', false));
        log_timing();
        header('Location:index.php');
        exit();
    case 'logout':
        user_logout();
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false) {
            header('Location:'.$_SERVER['HTTP_REFERER']);
        } else {
            header('Location:index.php');
        }
        log_timing();
        exit();
    case 'reg_done':
        $reg_status = user_register(POST('login'), POST('email', ''), POST('passwd'), POST('passwd_re'), POST('subscribe', 0));
        if ($reg_status == 1) {
            header("Location:index.php");
            exit();
        }
        else
            $smarty->assign('reg_status', $reg_status);
        break;
    case 'change_pw':
        $smarty->assign('change_status', user_change_password(POST('old_pw'), POST('new_pw'), POST('new_pw_re')));
        break;
    case 'change_email':
        $smarty->assign('change_status', user_change_email(POST('email'), POST('passwd')));
        break;
    case 'generate_passwd':
        $smarty->assign('gen_status', user_generate_password(POST('email')));
        break;
    case 'change_name':
        $smarty->assign('change_status', user_change_shown_name(POST('shown_name')));
        break;
}
log_timing();

if (
    isset($_SERVER['HTTP_REFERER']) &&
    strpos($_SERVER['HTTP_REFERER'], 'login.php') === false &&
    strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false
)
    $_SESSION['return_to'] = $_SERVER['HTTP_REFERER'];
else
    $_SESSION['return_to'] = 'index.php';

$smarty->assign('action', $action);
$smarty->display('login.tpl');

?>
