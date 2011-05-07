<?php
require('lib/header.php');
if (isset($_GET['act']))
    $action = $_GET['act'];
else $action = '';
if ($action=='login') {
    if (user_login(mysql_real_escape_string($_POST['login']), $_POST['passwd'])) {
        header('Location:'.$_SESSION['return_to']);
    } else {
        header('Location:login.php?act=error');
    }
    return;
} elseif ($action=='login_openid') {
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
    return;
} elseif ($action=='login_openid2') {
    user_login_openid_agree(isset($_POST['agree']));
    return;
} elseif ($action=='logout') {
    user_logout();
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false) {
        header('Location:'.$_SERVER['HTTP_REFERER']);
    } else {
        header('Location:index.php');
    }
    return;
} elseif ($action=='reg_done') {
    $smarty->assign('reg_status', user_register($_POST));
} elseif ($action=='change_pw') {
    $smarty->assign('change_status', user_change_password($_POST));
} elseif ($action=='change_email') {
    $smarty->assign('change_status', user_change_email($_POST));
} elseif ($action=='generate_passwd') {
    $smarty->assign('gen_status', user_generate_password($_POST['email']));
} elseif (isset($_SESSION['user_id'])) {
    header("Location:index.php");
    return;
}

if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false)
    $_SESSION['return_to'] = $_SERVER['HTTP_REFERER'];
else $_SESSION['return_to'] = 'index.php';

$smarty->display('login.tpl');
?>
