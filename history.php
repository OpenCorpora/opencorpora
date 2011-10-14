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

$smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
$smarty->setCacheLifetime(90);
$cache_id = "$sent_id@$set_id@$skip@$maa";

if (!$smarty->isCached('history.tpl', $cache_id)) {
    $smarty->assign('history', main_history($sent_id, $set_id, $skip, $maa));
    $smarty->assign('skip', $skip);
    $smarty->assign('maa', $maa);
}
$smarty->display('history.tpl', $cache_id);
?>
