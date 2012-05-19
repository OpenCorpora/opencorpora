<?php
require('lib/header.php');
require('lib/lib_dict.php');
if (isset($_GET['act']))
    $action = $_GET['act'];
else $action = '';

//check permissions
if (!in_array($action, array('', 'gram', 'gram_restr', 'lemmata', 'errata', 'edit')) &&
    !user_has_permission('perm_dict')) {
        show_error($config['msg']['notadmin']);
        return;
}

switch($action) {
    case 'add_gram':
        $name = mysql_real_escape_string($_POST['g_name']);
        $group = (int)$_POST['parent_gram'];
        $outer_id = mysql_real_escape_string($_POST['outer_id']);
        $descr = mysql_real_escape_string($_POST['descr']);
        add_grammem($name, $group, $outer_id, $descr);
        break;
    case 'move_gram':
        $grm = (int)$_GET['id'];
        $dir = $_GET['dir'];
        move_grammem($grm, $dir);
        break;
    case 'del_gram':
        $grm = (int)$_GET['id'];
        del_grammem($grm);
        break;
    case 'edit_gram':
        $id = (int)$_POST['id'];
        $inner_id = mysql_real_escape_string($_POST['inner_id']);
        $outer_id = mysql_real_escape_string($_POST['outer_id']);
        $descr = mysql_real_escape_string($_POST['descr']);
        edit_grammem($id, $inner_id, $outer_id, $descr);
        break;
    case 'clear_errata':
        clear_dict_errata(isset($_GET['old']));
        break;
    case 'not_error':
        mark_dict_error_ok((int)$_GET['error_id'], $_POST['comm']);
        break;
    case 'add_restr':
        add_dict_restriction($_POST);
        break;
    case 'del_restr':
        del_dict_restriction((int)$_GET['id']);
        break;
    case 'update_restr':
        calculate_gram_restrictions();
        break;
    case 'save':
        dict_save($_POST);
        break;
    case 'add_link':
        if (add_link((int)$_POST['from_id'], (int)$_POST['lemma_id'], (int)$_POST['link_type'])) {
            header("Location:dict.php?act=edit&id=".(int)$_POST['from_id']);
        } else
            show_error();
        break;
    case 'del_link':
        if (del_link((int)$_GET['id'])) {
            header("Location:dict.php?act=edit&id=".(int)$_GET['lemma_id']);
        } else
            show_error();
        break;
    case 'del_lemma':
        del_lemma((int)$_GET['lemma_id']);
        break;
    case 'lemmata':
        $smarty->assign('search', get_dict_search_results($_POST));
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
        $lid = (int)$_GET['id'];
        $smarty->assign('editor', get_lemma_editor($lid));
        $smarty->assign('link_types', get_link_types());
        $smarty->display('dict/lemma_edit.tpl');
        break;
    case 'errata':
        $smarty->assign('errata', get_dict_errata(isset($_GET['all']), isset($_GET['rand'])));
        $smarty->display('dict/errata.tpl');
        break;
    default:
        $smarty->assign('stats', get_dict_stats());
        $smarty->display('dict/main.tpl');
}
?>
