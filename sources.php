<?php
require('lib/header.php');
require_once('lib/lib_books.php');

if (!user_has_permission('perm_adder')) {
    show_error($config['msg']['notadmin']);
    return;
}

$action = isset($_GET['act']) ? $_GET['act'] : '';

switch($action) {
    case 'add':
        if (source_add($_POST['url'], $_POST['title'], (int)$_POST['parent'])) {
            header("Location:sources.php");
        } else {
            show_error();
        }
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
?>
