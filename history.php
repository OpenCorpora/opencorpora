<?php
require('lib/header.php');
require('lib/lib_history.php');
if (isset($_GET['sent_id']))
    $sent_id = (int)$_GET['sent_id'];
    else $sent_id = 0;
$smarty->assign('history', main_history($sent_id));
show_page('history.tpl');
?>
