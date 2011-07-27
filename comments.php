<?php
require('lib/header.php');
require('lib/lib_history.php');

$smarty->assign('comments', get_latest_comments(isset($_GET['skip']) ? $_GET['skip'] : 0));
$smarty->display('comments.tpl');
?>
