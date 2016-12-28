<?php
require('lib/header.php');
require_once('lib/constants.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_users.php');
require_once('lib/lib_morph_pools.php');

$action = GET('act', '');

switch ($action) {
    case 'add_type':
        add_morph_pool_type(POST('gram'), POST('descr'), POST('pool_name'));
        header("Location:pools.php?added&type=1");
        break;
    case 'delete':
        delete_morph_pool(GET('pool_id'));
        header("Location:pools.php");
        break;
    case 'candidates':
        $smarty->assign('data', get_pool_candidates_page(GET('pool_type')));
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
        save_morph_pool_types(POST('complexity'), POST('doc'));
        header("Location:pools.php?act=types");
        break;
    case 'samples':
        if (GET('tabs', 0)) {
            $pool_id = (int)GET('pool_id');
            $smarty->assign('pool', get_morph_samples_page($pool_id, true, 100));
            header("Content-type: application/csv; charset=utf-8");
            header("Content-disposition: attachment; filename=pool_{$pool_id}.tab");
            $smarty->display('qa/pool_tabs.tpl');
        }
        else {
            $filter = GET('filter', false);
            $matches = NULL;
            if ($filter && !user_has_permission(PERM_MORPH_MODER) && preg_match('/^user:(\d+)$/', $filter, $matches)) {
                if ($matches[1] != $_SESSION['user_id']) {
                    show_error("Можно просматривать только свои ответы.");
                    return;
                }
            }

            $smarty->assign('sortby', GET('sortby', ''));
            $smarty->assign('pool', get_morph_samples_page(
                GET('pool_id'),
                isset($_GET['ext']),  // should be GET('ext', 0), but saved in order for old links to work
                $config['misc']['morph_annot_moder_context_size'],
                GET('skip', 0),
                $filter,
                (!user_has_permission(PERM_MORPH_MODER) || OPTION(OPT_MODER_SPLIT) == 1) ? $config['misc']['morph_annot_moder_page_size'] : 0,
                GET('sortby', '')
            ));
            $smarty->display('qa/pool.tpl');
        }
        break;
    case 'promote':
        check_permission(PERM_MORPH_MODER);
        promote_samples((int)GET('pool_type'),
                        POST('type'),
                        (int)POST(POST('type')."_n"),
                        (int)POST('pools_num'),
                        $_SESSION['user_id']);
        header("Location:pools.php?type=2");
        break;
    case 'publish':
        publish_pool(GET('pool_id'));
        header("Location:pools.php?type=2");
        break;
    case 'unpublish':
        unpublish_pool(GET('pool_id'));
        header("Location:pools.php?act=samples&pool_id=".GET('pool_id'));
        break;
    case 'begin_moder':
        moderate_pool(GET('pool_id'));
        header("Location:pools.php?act=samples&pool_id=".GET('pool_id'));
        break;
    case 'agree':
        moder_agree_with_all(GET('pool_id'));
        header("Location:pools.php?act=samples&pool_id=".GET('pool_id'));
        break;
    case 'finish_moder':
        finish_moderate_pool(GET('pool_id'));
        header("Location:index.php?page=pool_charts");
        break;
    case 'finish_and_merge':
        $pool_id = GET('pool_id');
        finish_moderate_pool($pool_id);
        begin_pool_merge($pool_id);
        header("Location:index.php?page=pool_charts");
        break;
    case 'begin_merge':
        begin_pool_merge(GET('pool_id'));
        header("Location:index.php?page=pool_charts");
        break;
    default:
        $smarty->assign('moder_id', GET('moder_id', 0));
        $type = GET('type', 0);
        $smarty->assign('type', $type);
        if ($type == MA_POOLS_STATUS_FOUND_CANDIDATES) {
            $types = get_morph_pool_types(GET('filter'));
            uasort($types, function ($a, $b) {
                return $a['found_samples'] < $b['found_samples'] ? 1 : -1;
            });
            $smarty->assign('types', $types);
            $smarty->display('qa/pools_notready.tpl');
        } else {
            $smarty->assign('pools', get_morph_pools_page($type, (int)GET('moder_id', 0), GET('filter', '')));
            $smarty->display('qa/pools.tpl');
        }
}
log_timing();
