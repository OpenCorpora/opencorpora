<?php
require('lib/header.php');
require('lib/lib_history.php');
if (isset($_GET['sent_id']))
    $sent_id = (int)$_GET['sent_id'];
    else $sent_id = 0;
if (isset($_GET['set_id']))
    $set_id = (int)$_GET['set_id'];
    else $set_id = 0;
if (isset($_GET['skip']))
    $skip = (int)$_GET['skip'];
    else $skip = 0;
if (isset ($_GET['maa']))
    $maa = $_GET['maa'] ? 1 : 0;
    else $maa = 0;
if (isset($_GET['user_id']))
    $user_id = (int)$_GET['user_id'];
    else $user_id = 0;

$smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
$smarty->setCacheLifetime(90);
$cache_id = "$sent_id@$set_id@$skip@$maa@$user_id";

if (!is_cached('history.tpl', $cache_id)) {
    $smarty->assign('history', main_history($sent_id, $set_id, $skip, $maa, $user_id));
    $smarty->assign('skip', $skip);
    $smarty->assign('maa', $maa);
    $smarty->assign('user_id', $user_id);
}
$smarty->display('history.tpl', $cache_id);
log_timing();
?>
