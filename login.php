<?php
require('lib/header.php');

if (isset($_GET['act']))
    $action = $_GET['act'];
else $action = '';

if (isset($_SESSION['user_id']) && in_array($action, array('', 'login', 'login_openid', 'register'))) {
    header("Location:index.php");
    return;
}

switch($action) {
    case 'login':
        if (user_login(mysql_real_escape_string($_POST['login']), $_POST['passwd'])) {
            header('Location:'.$_SESSION['return_to']);
        } else {
            header('Location:login.php?act=error');
        }
        break;
    case 'login_openid':
        $r = user_login_openid($_POST['token'], isset($_POST['agree']));
        switch ($r) {
            case 1:
                header('Location:'.$_SESSION['return_to']);
                break;
            case 2:
                $smarty->display('openid_license.tpl');
                break;
            default:
                header('Location:login.php?act=error');
        }
        break;
    case 'login_openid2':
        if (user_login_openid_agree(isset($_POST['agree'])))
            header('Location:'.$_SESSION['return_to']);
        else
            show_error();
        break;
    case 'logout':
        if (!user_logout()) {
            show_error();
            break;
        }

        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false) {
            header('Location:'.$_SERVER['HTTP_REFERER']);
        } else {
            header('Location:index.php');
        }
        break;
    case 'reg_done':
        $reg_status = user_register($_POST);
        if ($reg_status == 1)
            header("Location:index.php");
        else
            $smarty->assign('reg_status', $reg_status);
        break;
    case 'change_pw':
        $smarty->assign('change_status', user_change_password($_POST));
        break;
    case 'change_email':
        $smarty->assign('change_status', user_change_email($_POST));
        break;
    case 'generate_passwd':
        $smarty->assign('gen_status', user_generate_password($_POST['email']));
        break;
    case 'change_name':
        $smarty->assign('change_status', user_change_shown_name($_POST['shown_name']));
        break;
}

if (
    isset($_SERVER['HTTP_REFERER']) &&
    strpos($_SERVER['HTTP_REFERER'], 'login.php') === false &&
    strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'] !== false)
)
    $_SESSION['return_to'] = $_SERVER['HTTP_REFERER'];
else
    $_SESSION['return_to'] = 'index.php';

$smarty->assign('action', $action);
$smarty->display('login.tpl');

?>
