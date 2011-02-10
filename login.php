<?php
require('lib/header.php');
if (isset($_GET['act']))
    $action = $_GET['act'];
else $action = '';
if ($action=='login') {
    if (user_login(mysql_real_escape_string($_POST['login']), $_POST['passwd'])) {
        header('Location:'.$_SESSION['return_to']);
        return;
    } else {
        header('Location:login.php?act=error');
        return;
    }
} elseif ($action=='logout') {
    user_logout();
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false) {
        header('Location:'.$_SERVER['HTTP_REFERER']);
        return;
    } else {
        header('Location:index.php');
        return;
    }
} elseif ($action=='reg_done') {
    $smarty->assign('reg_status', user_register($_POST));
} elseif ($action=='change_pw') {
    $smarty->assign('change_status', user_change_password($_POST));
} elseif ($action=='change_email') {
    $smarty->assign('change_status', user_change_email($_POST));
} elseif (isset($_SESSION['user_id'])) {
    header("Location:index.php");
    return;
}

if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false)
    $_SESSION['return_to'] = $_SERVER['HTTP_REFERER'];
else $_SESSION['return_to'] = 'index.php';

$smarty->display('login.tpl');
?>
