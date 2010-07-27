<?php
require('lib/header.php');
require('lib/lib_history.php');
if (isset($_GET['lemma_id']))
    $lemma_id = (int)$_GET['lemma_id'];
    else $lemma_id = 0;
$smarty->assign('history', dict_history($lemma_id));
$smarty->display('dict_history.tpl');
?>
