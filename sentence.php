<?php
require_once('lib/header.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_annot.php');
require_once('lib/lib_syntax.php');
require_once('lib/lib_dict.php');
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    header('Location:index.php');
    return;
}

$action = isset($_GET['act']) ? $_GET['act'] : '';
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'morph';

switch ($action) {
    case 'save':
        if (user_has_permission('perm_disamb')) {
            if (sentence_save($id)) {
                header("Location:sentence.php?id=$id");
            } else {
                show_error();
            }
            break;
        } else {
            show_error($config['msg']['notlogged']);
        }
        break;
    case 'save_src':
        if (is_admin() && sentence_save_source($id, $_POST['src_text'])) {
            header("Location:sentence.php?id=$id");
        } else
            show_error();
        break;
    default:
        $smarty->assign('sentence', get_sentence($id));
        if ($mode == 'syntax') {
            $smarty->assign('group_types', get_syntax_group_types());
            $smarty->assign('groups', get_groups_by_sentence($id, $_SESSION['user_id']));
            $smarty->display('sentence_syntax.tpl');
        } else
            $smarty->display('sentence.tpl');
}
