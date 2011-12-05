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
                if (isset($_GET['id']) && sentence_save($sent_id = (int)$_GET['id'])) {
                    header("Location:sentence.php?id=$sent_id");
                } else {
                    show_error();
                }
                break;
            } else {
                show_error($config['msg']['notlogged']);
            }
    }
} else {
    $smarty->assign('sentence', get_sentence($id));
    $smarty->display('sentence.tpl');
}
?>
