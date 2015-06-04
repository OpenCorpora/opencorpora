<?php
require('lib/header.php');
require('lib/lib_dict.php');
if (isset($_GET['act']))
    $action = $_GET['act'];
else $action = '';

$smarty->assign('active_page', 'dict');

//check permissions
if (!in_array($action, array('', 'gram', 'gram_restr', 'lemmata', 'errata', 'edit', 'absent')) &&
    !user_has_permission(PERM_DICT)) {
        show_error($config['msg']['notadmin']);
        return;
}

switch ($action) {
    case 'add_gram':
        add_grammem($_POST['g_name'], $_POST['parent_gram'], $_POST['outer_id'], $_POST['descr']);
        header("Location:dict.php?act=gram");
        break;
    case 'move_gram':
        move_grammem($_GET['id'], $_GET['dir']);
        header('Location:dict.php?act=gram#g'.$grm_id);
        break;
    case 'del_gram':
        del_grammem($_GET['id']);
        header("Location:dict.php?act=gram");
        break;
    case 'edit_gram':
        $id = $_POST['id'];
        $inner_id = $_POST['inner_id'];
        $outer_id = $_POST['outer_id'];
        $descr = $_POST['descr'];
        edit_grammem($id, $inner_id, $outer_id, $descr);
        header('Location:dict.php?act=gram');
        break;
    case 'clear_errata':
        clear_dict_errata(isset($_GET['old']));
        header("Location:dict.php?act=errata");
        break;
    case 'not_error':
        mark_dict_error_ok($_GET['error_id'], $_POST['comm']);
        header("Location:dict.php?act=errata");
        break;
    case 'add_restr':
        add_dict_restriction($_POST);
        header("Location:dict.php?act=gram_restr");
        break;
    case 'del_restr':
        del_dict_restriction($_GET['id']);
        header("Location:dict.php?act=gram_restr");
        break;
    case 'update_restr':
        calculate_gram_restrictions();
        header("Location:dict.php?act=gram_restr");
        break;
    case 'save':
        // update after selectpicker (lemma_edit.tpl)
        // now we have to implode the arrays
        if (!empty($_POST['form_gram']))
            foreach ($_POST['form_gram'] as &$grams) {
                $grams = implode(', ', $grams);
            }
        if (!empty($_POST['lemma_gram']))
            $_POST['lemma_gram'] = implode(', ', $_POST['lemma_gram']);

        $lemma_id = dict_save($_POST);
        header("Location:dict.php?act=edit&saved&id=$lemma_id");
        break;
    case 'add_link':
        add_link($_POST['from_id'], $_POST['lemma_id'], $_POST['link_type']);
        header("Location:dict.php?act=edit&id=".$_POST['from_id']);
        break;
    case 'del_link':
        del_link($_GET['id']);
        header("Location:dict.php?act=edit&id=".$_GET['lemma_id']);
        break;
    case 'change_link_dir':
        change_link_direction($_GET['id']);
        header("Location:dict.php?act=edit&id=".$_GET['lemma_id']);
        break;
    case 'del_lemma':
        del_lemma($_GET['lemma_id']);
        header("Location:dict.php");
        break;
    case 'lemmata':
        $smarty->assign('search', get_dict_search_results($_GET));
        $smarty->display('dict/lemmata.tpl');
        break;
    case 'gram':
        $order = isset($_GET['order']) ? $_GET['order'] : '';
        $smarty->assign('grammems', get_grammem_editor($order));
        $smarty->assign('order', $order);
        $smarty->assign('select', dict_get_select_gram());
        $smarty->display('dict/gram.tpl');
        break;
    case 'gram_restr':
        $smarty->assign('restrictions', get_gram_restrictions(isset($_GET['hide_auto'])));
        $smarty->display('dict/restrictions.tpl');
        break;
    case 'edit':
        $smarty->assign('editor', get_lemma_editor($_GET['id']));
        $smarty->assign('link_types', get_link_types());
        $smarty->assign('possible_grammems', dict_get_select_gram());
        $smarty->display('dict/lemma_edit.tpl');
        break;
    case 'errata':
        $smarty->assign('errata', get_dict_errata(isset($_GET['all']), isset($_GET['rand'])));
        $smarty->display('dict/errata.tpl');
        break;
    case 'pending':
        $skip = isset($_GET['skip']) ? $_GET['skip'] : 0;
        $smarty->assign('data', get_pending_updates($skip));
        $smarty->display('dict/pending.tpl');
        break;
    case 'reannot':
        update_pending_tokens($_POST['rev_id']);
        header("Location:dict.php?act=pending");
        break;
    case 'absent':
        $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
        $smarty->setCacheLifetime(3600);
        if (!is_cached('dict/absent.tpl'))
            $smarty->assign('words', get_top_absent_words());
        $smarty->display('dict/absent.tpl');
        break;
    default:

        $smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
        $smarty->setCacheLifetime(600);
        if (!is_cached('dict/main.tpl', (int)user_has_permission(PERM_DICT))) {
            $smarty->assign('stats', get_dict_stats());
            $smarty->assign('dl', get_downloads_info());
        }
        $smarty->display('dict/main.tpl', (int)user_has_permission(PERM_DICT));
}
log_timing();
?>
