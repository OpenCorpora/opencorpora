<?php
/*
Since this configuration file is environment-dependent,
it should be written manually on checkout. You should
copy this template as config.php and change the appropriate
values. You should NOT svn add it.
*/

$config['web_prefix'] = ''; # '' or '/dir'

$config['mysql_host']   = 'localhost';
$config['mysql_dbname'] = '';
$config['mysql_user']   = '';
$config['mysql_passwd'] = '';

#message text
$config['msg_loginerror'] = "Пользователь с указанной комбинацией логина и пароля не найден. Попробуйте, пожалуйста, <a href='?'>ещё раз</a>.";

$config['msg_notadmin'] = 'У вас недостаточно прав для просмотра этой страницы. Возможно, вам необходимо <a href="login.php">войти</a> под своим именем и паролем.';
$config['msg_notlogged'] = 'Для просмотра этой страницы необходимо <a href="login.php">войти</a> под своим именем и паролем.';
?>
