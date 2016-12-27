<?php
require('lib/header.php');
require('lib/lib_history.php');

$sent_id = GET('sent_id', 0);
$set_id = GET('set_id', 0);
$skip = (int)GET('skip', 0);
$maa = GET('maa', 0) ? 1 : 0;
$user_id = (int)GET('user_id', 0);

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
