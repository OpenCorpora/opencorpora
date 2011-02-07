<?php
require('lib/header.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_history.php');
$sent_id = (int)$_GET['sent_id'];
$set_id = (int)$_GET['set_id'];
$smarty->assign('diff', main_diff($sent_id, $set_id));
show_page('diff.tpl');
?>
