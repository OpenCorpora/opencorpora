<?php
require('lib/header.php');
require_once('lib/lib_annot.php');

if (!isset($_GET['pool_type'])) {
    show_error();
    return;
}

$smarty->assign('content', get_wiki_page(get_pool_manual_page((int)$_GET['pool_type'])));
$smarty->display('static/doc/annotation.tpl');
?>
