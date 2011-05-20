<?php
require('lib/header.php');
require('lib/lib_xml.php');
require('lib/lib_annot.php');
require('lib/lib_dict.php');
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    header('Location:index.php');
    return;
}
if (isset($_GET['act'])) {
    $action = $_GET['act'];
    switch($action) {
        case 'save':
            if (user_has_permission('perm_disamb')) {
                sentence_save();
                break;
            } else {
                show_error($config['msg_notlogged']);
            }
    }
} else {
    $smarty->assign('sentence', get_sentence($id));
    $smarty->display('sentence.tpl');
}
?>
