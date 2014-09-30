<?php
require('lib/header.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_history.php');
$sent_id = isset($_GET['sent_id']) ? (int)$_GET['sent_id'] : 0;
$set_id = isset($_GET['set_id']) ? (int)$_GET['set_id'] : 0;
$rev_id = isset($_GET['rev_id']) ? (int)$_GET['rev_id'] : 0;
$smarty->assign('diff', main_diff($sent_id, $set_id, $rev_id));
$smarty->display('diff.tpl');
log_timing();
?>
