<?php
require('lib/header.php');
require_once('lib/lib_users.php');

$smarty->assign('user', get_user_info((int)$_GET['id']));
$smarty->display('user.tpl');
?>
