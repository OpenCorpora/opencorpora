<?php
require('lib/header.php');
require_once('lib/lib_users.php');
require_once('lib/lib_awards.php');
require_once('lib/lib_achievements.php');

$id = $_GET['id'] ?: $_SESSION['user_id'];

$smarty->assign('user', get_user_info($id));
$smarty->assign('user_id', $id);

$smarty->assign('complexity', array(
    0 => 'Сложность неизвестна',
    1 => 'Очень простые задания',
    2 => 'Простые задания',
    3 => 'Сложные задания',
    4 => 'Очень сложные задания'));

$am2 = new AchievementsManager($id);
$smarty->assign('achievements', $a = $am2->pull_all());

$smarty->display('user.tpl');
log_timing();
