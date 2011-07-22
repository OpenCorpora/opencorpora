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
        $smarty->assign('sources', get_sources_page(isset($_GET['my']), isset($_GET['active'])));
        $smarty->display('templates/sources.tpl');
}
?>
