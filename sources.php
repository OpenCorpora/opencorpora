<?php
require('lib/header.php');
require_once('lib/lib_books.php');

if (!user_has_permission('perm_adder')) {
    show_error($config['msg_notadmin']);
    return;
}

$action = isset($_GET['act']) ? $_GET['act'] : '';

switch($action) {
    case 'add':
        source_add($_POST['url'], $_POST['title'], (int)$_POST['parent']);
        break;
    default:
        $what = isset($_GET['what']) ? $_GET['what'] : '';
        $skip = isset($_GET['skip']) ? $_GET['skip'] : 0;
        $smarty->assign('sources', get_sources_page($skip, $what));
        $smarty->assign('what', $what);
        $smarty->assign('skip', $skip);
        $smarty->display('templates/sources.tpl');
}
?>
