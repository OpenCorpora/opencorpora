<?php
require('lib/header.php');
require('lib/lib_history.php');

$smarty->assign('comments', get_latest_comments());
$smarty->display('comments.tpl');
?>
