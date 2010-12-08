<?php
require('lib/header.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_history.php');
$lemma_id = (int)$_GET['lemma_id'];
$set_id = (int)$_GET['set_id'];
$smarty->assign('diff', dict_diff($lemma_id, $set_id));
$smarty->display('dict/diff.tpl');
?>
