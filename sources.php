<?php
require('lib/header.php');
require_once('lib/lib_books.php');

$action = isset($_GET['act']) ? $_GET['act'] : '';

switch ($action) {
    case 'add':
        source_add($_POST['url'], $_POST['title'], $_POST['parent']);
        header("Location:sources.php");
        break;
    default:
        $what = isset($_GET['what']) ? $_GET['what'] : '';
        $skip = isset($_GET['skip']) ? $_GET['skip'] : 0;
        $src  = isset($_GET['src'])  ? $_GET['src']  : 0;
        $smarty->assign('sources', get_sources_page($skip, $what, $src));
        $smarty->assign('what', $what);
        $smarty->assign('skip', $skip);
        $smarty->display('templates/sources.tpl');
}
log_timing();
?>
