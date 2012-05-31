<?php
require('lib/header.php');
require_once('lib/lib_qa.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_annot.php');

if (!is_logged()) {
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
            $smarty->display('qa/morph_annot_thanks.tpl');
        }
        break;
    case 'my':
        if (!isset($_GET['pool_id']) || !$_GET['pool_id']) {
            show_error('Wrong pool_id');
            break;
        }
        if ($t = get_my_answers((int)$_GET['pool_id'], 0)) {
            $smarty->assign('packet', $t);
            $smarty->display('qa/morph_annot.tpl');
        } else {
            show_error("Не нашлось примеров.");
        }
        break;
    default:
        $smarty->assign('available', get_available_tasks($_SESSION['user_id']));
        $smarty->display('qa/tasks.tpl');
}
?>
