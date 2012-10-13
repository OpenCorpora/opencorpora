<?php
require('lib/header.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_annot.php');

$action = isset($_GET['act']) ? $_GET['act'] : '';

if ($action && !in_array($action, array('samples', 'candidates')) && !user_has_permission('perm_check_morph')) {
    show_error($config['msg']['notadmin']);
    return;
}

// temporarily hidden pools
if (isset($_GET['pool_id']) && in_array((int)$_GET['pool_id'], array(19, 23, 29, 46))) {
    show_error("Этот пул временно скрыт.");
    return;
}

switch ($action) {
    case 'add':
        if (add_morph_pool()) {
            header("Location:pools.php?added&type=0");
        } else {
            show_error();
        }
        break;
    case 'delete':
        if (delete_morph_pool((int)$_GET['pool_id'])) {
            header("Location:pools.php");
        } else {
            show_error("Ошибка. Возможно, пул содержит пользовательские ответы.");
        }
        break;
    case 'candidates':
        $smarty->assign('pool', get_pool_candidates_page((int)$_GET['pool_id']));
        $smarty->display('qa/pool_candidates.tpl');
        break;
    case 'samples':
        $smarty->assign('pool', get_morph_samples_page(
            (int)$_GET['pool_id'],
            isset($_GET['ext']),
            isset($_GET['disagreed']),
            isset($_GET['nomod']),
            isset($_GET['with_comments']),
            isset($_GET['not_ok'])
        ));
        if (isset($_GET['tabs'])) {
            $smarty->assign('pool', get_morph_samples_page((int)$_GET['pool_id'], true, 100, false, false, false, false));
            header("Content-type: application/csv; charset=utf-8");
            header("Content-disposition: attachment; filename=pool_".(int)$_GET['pool_id'].".tab");
            $smarty->display('qa/pool_tabs.tpl');
        }
        else {
            $smarty->assign('pool', get_morph_samples_page(
                (int)$_GET['pool_id'],
                isset($_GET['ext']),
                4,
                isset($_GET['disagreed']),
                isset($_GET['nomod']),
                isset($_GET['with_comments']),
                isset($_GET['not_ok'])
            ));
            $smarty->display('qa/pool.tpl');
        }
        break;
    case 'promote':
        if (promote_samples((int)$_GET['pool_id'], $_POST['type'])) {
            header("Location:pools.php?act=samples&pool_id=".(int)$_GET['pool_id']);
        } else {
            show_error();
        }
        break;
    case 'publish':
        if (publish_pool((int)$_GET['pool_id'])) {
            header("Location:pools.php?act=samples&pool_id=".(int)$_GET['pool_id']);
        } else {
            show_error();
        }
        break;
    case 'unpublish':
        if (unpublish_pool((int)$_GET['pool_id'])) {
            header("Location:pools.php?act=samples&pool_id=".(int)$_GET['pool_id']);
        } else {
            show_error();
        }
        break;
    case 'begin_moder':
        if (moderate_pool((int)$_GET['pool_id'])) {
            header("Location:pools.php?act=samples&pool_id=".(int)$_GET['pool_id']);
        } else {
            show_error("Ошибка. Возможно, пул не полностью заполнен.");
        }
        break;
    case 'finish_moder':
        if (finish_moderate_pool((int)$_GET['pool_id']))
            header("Location:pools.php?act=samples&pool_id=".(int)$_GET['pool_id']);
        else
            show_error("Ошибка. Убедитесь, что все примеры отмодерированы и что вы являетесь модератором пула.");
        break;
    case 'begin_merge':
        if (begin_pool_merge((int)$_GET['pool_id']))
            header("Location:pools.php?act=samples&pool_id=".(int)$_GET['pool_id']);
        else
            show_error();
        break;
    default:
        $smarty->assign('pools', get_morph_pools_page((int)$_GET['type']));
        $smarty->display('qa/pools.tpl');
}
