<?php
require('lib/header.php');
require_once('lib/constants.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_morph_pools.php');

$action = isset($_GET['act']) ? $_GET['act'] : '';

switch ($action) {
    case 'add_type':
        add_morph_pool_type($_POST['gram'], $_POST['descr'], $_POST['pool_name']);
        header("Location:pools.php?added&type=1");
        break;
    case 'delete':
        delete_morph_pool($_GET['pool_id']);
        header("Location:pools.php");
        break;
    case 'candidates':
        $smarty->assign('data', get_pool_candidates_page($_GET['pool_type']));
        $smarty->assign('default_size', MA_DEFAULT_POOL_SIZE);
        $smarty->display('qa/pool_candidates.tpl');
        break;
    case 'types':
        // TODO move to common listing page (type='')
        check_permission(PERM_MORPH_MODER);
        $smarty->assign('data', get_morph_pool_types());
        $smarty->display('qa/pool_types.tpl');
        break;
    case 'save_types':
        save_morph_pool_types($_POST);
        header("Location:pools.php?act=types");
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
            if ($filter && !user_has_permission(PERM_MORPH_MODER) && preg_match('/^user:(\d+)$/', $filter, $matches)) {
                if ($matches[1] != $_SESSION['user_id']) {
                    show_error("Можно просматривать только свои ответы.");
                    return;
                }
            }

            $smarty->assign('sortby', isset($_GET['sortby']) ? $_GET['sortby'] : '');
            $smarty->assign('pool', get_morph_samples_page(
                $_GET['pool_id'],
                isset($_GET['ext']),
                $config['misc']['morph_annot_moder_context_size'],
                isset($_GET['skip']) ? $_GET['skip'] : 0,
                $filter,
                (!user_has_permission(PERM_MORPH_MODER) || $_SESSION['options'][4] == 1) ? $config['misc']['morph_annot_moder_page_size'] : 0,
                isset($_GET['sortby']) ? $_GET['sortby'] : ''
            ));
            $smarty->display('qa/pool.tpl');
        }
        break;
    case 'promote':
        check_permission(PERM_MORPH_MODER);
        promote_samples((int)$_GET['pool_type'],
                        $_POST['type'],
                        (int)$_POST[$_POST['type']."_n"],
                        (int)$_POST['pools_num'],
                        $_SESSION['user_id']);
        header("Location:pools.php?type=2");
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
        $smarty->assign('moder_id', isset($GET['moder_id']) ? $_GET['moder_id'] : 0);
        $type = isset($_GET['type']) ? $_GET['type'] : 0;
        $smarty->assign('type', $type);
        if ($type == MA_POOLS_STATUS_FOUND_CANDIDATES) {
            $types = get_morph_pool_types($_GET['filter']);
            uasort($types, function ($a, $b) {
                return $a['found_samples'] < $b['found_samples'] ? 1 : -1;
            });
            $smarty->assign('types', $types);
            $smarty->display('qa/pools_notready.tpl');
        } else {
            $smarty->assign('pools', get_morph_pools_page($type, (int)$_GET['moder_id'], $_GET['filter']));
            $smarty->display('qa/pools.tpl');
        }
}
log_timing();
