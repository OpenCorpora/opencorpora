<?php
require('lib/header.php');
$action = $_GET['act'];
if ($action=='login') {
    if (user_login(mysql_real_escape_string($_POST['login']), $_POST['passwd'])) {
        header('Location:index.php');
    } else {
        header('Location:login.php?act=error');
    }
} elseif ($action=='logout') {
    user_logout();
    header('Location:index.php');
}
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='<?$config['web_prefix']?>/css/main.css'/>
</head>
<body>
<?php require('include/_header.php'); ?>
<div id='content'>
<?php
switch ($action) {
    case 'error':
        print $config['msg_loginerror'];
        break;
    case 'register':?>
<form action="?act=reg_done" method='post' id='login_form'>
<table cellspacing='5'>
<tr><td>Имя пользователя<td><input type='text' name='login' size='40' maxlength='50'/></tr>
<tr><td>Пароль<td><input type='password' name='passwd' size='40' maxlength='50'/></tr>
<tr><td>Пароль ещё раз<td><input type='password' name='passwd_re' size='40' maxlength='50'/></tr>
<tr><td>Email<br/><span class='small'>(необязательно)</span><td valign='top'><input type='text' name='email' size='40' maxlength='50'/></tr>
<tr><td colspan='2' align='right'><input type='submit' value='Зарегистрироваться'/></tr>
</table>
</form>
    <?
        break;
    case 'reg_done':
        if ($_POST['passwd'] != $_POST['passwd_re']) {
            print "Введённые пароли не совпадают. Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.";
            break;
        }
        $name = mysql_real_escape_string($_POST['login']);
        $passwd = md5(md5($_POST['passwd']).substr($name, 0, 2));
        $email = mysql_real_escape_string($_POST['email']);
        if (sql_query("INSERT INTO `users` VALUES(NULL, '$name', '$passwd', '1', '$email', '".time()."')")) {
            print "Спасибо, регистрация успешно завершена. Теперь вы можете <a href='?'>войти</a> под своим именем пользователя.";
        } else {
            print "Ошибка :(";
        }
        break;
    default:?>
<form action="?act=login" method="post" id='login_form'>    
<table cellspacing='2'>
<tr><td>Имя пользователя<td><input type='text' name='login' size='20' maxlength='50'/></tr>
<tr><td>Пароль<td><input type='password' name='passwd' size='20' maxlength='50'/></tr>
<tr><td><td><input type='submit' value='Войти'/>
<tr><td colspan='2'>или <a href='?act=register'>зарегистрироваться</a></tr>
</table>
</form>
<?
}
?>
</div><div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
