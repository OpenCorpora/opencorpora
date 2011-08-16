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
$smarty->assign('history', main_history($sent_id, $set_id, $skip));
$smarty->display('history.tpl');
?>
