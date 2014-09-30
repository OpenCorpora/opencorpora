<?php
require('lib/header.php');
require_once('lib/lib_users.php');
require_once('lib/lib_awards.php');

$id = $_GET['id'];
$smarty->assign('user', get_user_info($id));
$smarty->assign('complexity',array(
    0 => 'Сложность неизвестна',
    1 => 'Очень простые задания',
    2 => 'Простые задания',
    3 => 'Сложные задания',
    4 => 'Очень сложные задания'));
$smarty->assign('badges', get_user_badges($id));
$smarty->display('user.tpl');
log_timing();
?>
