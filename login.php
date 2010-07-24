<?php
require('lib/header.php');
$action = $_GET['act'];
if ($action=='login') {
    if (user_login(mysql_real_escape_string($_POST['login']), $_POST['passwd'])) {
        header('Location:'.$_SESSION['return_to']);
    } else {
        header('Location:login.php?act=error');
    }
} elseif ($action=='logout') {
    user_logout();
    header('Location:index.php');
} elseif ($action=='reg_done') {
    $smarty->assign('reg_status', user_register($_POST));
}
$_SESSION['return_to'] = $_SERVER['HTTP_REFERER'];
$smarty->display('login.tpl');
?>
