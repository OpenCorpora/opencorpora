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
} elseif ($action=='logout') {
    user_logout();
    if (isset($_SERVER['HTTP_REFERER']))
        header('Location:'.$_SERVER['HTTP_REFERER']);
    else header('Location:/');
} elseif ($action=='reg_done') {
    $smarty->assign('reg_status', user_register($_POST));
}

if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false)
    $_SESSION['return_to'] = $_SERVER['HTTP_REFERER'];
else $_SESSION['return_to'] = '/';

$smarty->display('login.tpl');
?>
