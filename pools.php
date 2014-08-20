<?php
require('lib/header.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_annot.php');

$action = isset($_GET['act']) ? $_GET['act'] : '';

if ($action && !in_array($action, array('samples', 'candidates')) && !user_has_permission('perm_check_morph')) {
    show_error($config['msg']['notadmin']);
    return;
}

switch ($action) {
    case 'add':
        add_morph_pool();
        header("Location:pools.php?added&type=0");
        break;
    case 'delete':
        delete_morph_pool($_GET['pool_id']);
        header("Location:pools.php");
        break;
    case 'candidates':
        $smarty->assign('pool', get_pool_candidates_page($_GET['pool_id']));
        $smarty->display('qa/pool_candidates.tpl');
        break;
    case 'samples':
        if (isset($_GET['tabs'])) {
            $smarty->assign('pool', get_morph_samples_page($_GET['pool_id'], true, 100));
            header("Content-type: application/csv; charset=utf-8");
            header("Content-disposition: attachment; filename=pool_".(int)$_GET['pool_id'].".tab");
            $smarty->display('qa/pool_tabs.tpl');
        }
        else {
            $filter = isset($_GET['filter']) ? $_GET['filter'] : false;
            $matches = NULL;
            if ($filter && !user_has_permission('perm_check_morph') && preg_match('/^user:(\d+)$/', $filter, $matches)) {
                if ($matches[1] != $_SESSION['user_id']) {
                    show_error("Можно просматривать только свои ответы.");
                    return;
                }
            }

            $smarty->assign('pool', get_morph_samples_page(
                $_GET['pool_id'],
                isset($_GET['ext']),
                $config['misc']['morph_annot_moder_context_size'],
                isset($_GET['skip']) ? $_GET['skip'] : 0,
                $filter,
                (!user_has_permission('perm_check_morph') || $_SESSION['options'][4] == 1) ? $config['misc']['morph_annot_moder_page_size'] : 0
            ));
            $smarty->display('qa/pool.tpl');
        }
        break;
    case 'promote':
        promote_samples((int)$_GET['pool_id'], $_POST['type']);
        header("Location:pools.php?act=samples&pool_id=".$_GET['pool_id']);
        break;
    case 'publish':
        publish_pool($_GET['pool_id']);
        header("Location:pools.php?act=samples&pool_id=".$_GET['pool_id']);
        break;
    case 'unpublish':
        unpublish_pool($_GET['pool_id']);
        header("Location:pools.php?act=samples&pool_id=".$_GET['pool_id']);
        break;
    case 'begin_moder':
        moderate_pool($_GET['pool_id']);
        header("Location:pools.php?act=samples&pool_id=".$_GET['pool_id']);
        break;
    case 'agree':
        moder_agree_with_all($_GET['pool_id']);
        header("Location:pools.php?act=samples&pool_id=".$_GET['pool_id']);
        break;
    case 'finish_moder':
        finish_moderate_pool($_GET['pool_id']);
        header("Location:index.php?page=pool_charts");
        break;
    case 'begin_merge':
        begin_pool_merge($_GET['pool_id']);
        header("Location:index.php?page=pool_charts");
        break;
    default:
        $smarty->assign('statuses', array(
        ));
        $smarty->assign('pools', get_morph_pools_page((int)$_GET['type'], (int)$_GET['moder_id'], $_GET['filter']));
        $smarty->display('qa/pools.tpl');
}
