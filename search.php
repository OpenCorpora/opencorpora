<?php
require('lib/header.php');
require('lib/lib_annot.php');

$search = mb_strtolower(GET('q'));
$smarty->assign('search', get_search_results($search, GET('exact_form', true)));
$smarty->display('search.tpl');
log_timing();
