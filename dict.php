<?php
require('lib/header.php');
require('lib/lib_dict.php');
if (isset($_GET['act']))
    $action = $_GET['act'];
else $action = '';
switch($action) {
    case 'add_gg':
        if (is_admin()) {
            $name = mysql_real_escape_string($_POST['g_name']);
            add_gramtype($name);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'move_gg':
        if (is_admin()) {
            $group = (int)$_GET['id'];
            $dir = $_GET['dir'];
            move_gramtype($group, $dir);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'del_gg':
        if (is_admin()) {
            $group = (int)$_GET['id'];
            del_gramtype($group);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'add_gram':
        if (is_admin()) {
            $name = mysql_real_escape_string($_POST['g_name']);
            $group = (int)$_POST['group'];
            $aot_id = mysql_real_escape_string($_POST['aot_id']);
            $descr = mysql_real_escape_string($_POST['descr']);
            add_grammem($name, $group, $aot_id, $descr);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'move_gram':
        if (is_admin()) {
            $grm = (int)$_GET['id'];
            $dir = $_GET['dir'];
            move_grammem($grm, $dir);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'del_gram':
        if (is_admin()) {
            $grm = (int)$_GET['id'];
            del_grammem($grm);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'edit_gram':
        if (is_admin()) {
            $id = (int)$_POST['id'];
            $inner_id = mysql_real_escape_string($_POST['inner_id']);
            $outer_id = mysql_real_escape_string($_POST['outer_id']);
            $descr = mysql_real_escape_string($_POST['descr']);
            edit_grammem($id, $inner_id, $outer_id, $descr);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'save':
        if (is_admin()) {
            dict_save($_POST);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'del_lemma':
        if (is_admin()) {
            del_lemma((int)$_GET['lemma_id']);
        } else
            show_error($config['msg_notadmin']);
        break;
    case 'lemmata':
        $smarty->assign('search', get_dict_search_results($_POST));
        $smarty->display('dict_lemmata.tpl');
        break;
    case 'gram':
        $smarty->assign('editor', get_grammem_editor());
        $smarty->display('gram.tpl');
        break;
    case 'edit':
        $lid = (int)$_GET['id'];
        $smarty->assign('editor', get_lemma_editor($lid));
        $smarty->display('dict_lemma_edit.tpl');
        break;
    default:
        $smarty->assign('stats', get_dict_stats());
        $smarty->display('dict_main.tpl');
}
?>
