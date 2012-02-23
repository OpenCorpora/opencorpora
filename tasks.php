<?php
require('lib/header.php');
require_once('lib/lib_qa.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_annot.php');

if (!user_has_permission('perm_disamb')) {
    show_error($config['msg']['notadmin']);
    return;
}

$action = isset($_GET['act']) ? $_GET['act'] : '';

switch($action) {
    case 'annot':
        if (!isset($_GET['pool_id']) || !$_GET['pool_id']) {
            show_error('Wrong pool_id');
            break;
        }
        if ($t = get_annotation_packet((int)$_GET['pool_id'], 5)) {
            $smarty->assign('packet', $t);
            $smarty->display('qa/morph_annot.tpl');
        } else {
            show_error("Ошибка. Возможно, кончились доступные задания.");
        }
        break;
    default:
        $smarty->assign('available', get_available_tasks($_SESSION['user_id']));
        $smarty->display('qa/tasks.tpl');
}
?>
