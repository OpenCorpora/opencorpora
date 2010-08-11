<?php
require('lib/header.php');
require('lib/lib_dict.php');
$action = $_GET['act'];
if (is_admin()) {
    switch($action) {
        case 'add_gg':
            $name = mysql_real_escape_string($_POST['g_name']);
            add_gramtype($name);
            break;
        case 'add_gram':
            $name = mysql_real_escape_string($_POST['g_name']);
            $group = (int)$_POST['group'];
            $aot_id = mysql_real_escape_string($_POST['aot_id']);
            $descr = mysql_real_escape_string($_POST['descr']);
            add_grammem($name, $group, $aot_id, $descr);
            break;
        case 'save':
            dict_save($_POST);
            break;
        case 'lemmata':
            $smarty->assign('search', get_dict_search_results($_POST));
            $smarty->display('dict_lemmata.tpl');
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
} else {
    die ($config['msg_notadmin']);
}
if (is_admin()) {
    switch($action) {
        case 'gram':
            print dict_page_gram();
            break;
    }
}
?>
