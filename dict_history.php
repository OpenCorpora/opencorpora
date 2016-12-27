<?php
require('lib/header.php');
require('lib/lib_history.php');
$lemma_id = (int)GET('lemma_id', 0);
$skip = (int)GET('skip', 0);
$smarty->assign('history', dict_history($lemma_id, $skip));
$smarty->assign('skip', $skip);
$smarty->display('dict/history.tpl');
log_timing();
?>
