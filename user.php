<?php
require('lib/header.php');
require_once('lib/lib_users.php');
require_once('lib/lib_awards.php');

$id = (int)$_GET['id'];
$smarty->assign('user', get_user_info($id));
$smarty->assign('badges', get_user_badges($id));
$smarty->display('user.tpl');
?>
