<?php
require('lib/header.php');
require('lib/lib_xml.php');
require('lib/lib_annot.php');
require('lib/lib_dict.php');
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    header('Location:index.php');
}
if (isset($_GET['act'])) {
    $action = $_GET['act'];
    switch($action) {
        case 'save':
            if (is_logged()) {
                sentence_save();
                break;
            } else {
                die ($config['msg_notlogged']);
            }
    }
} else {
    $smarty->assign('sentence', get_sentence($id));
    $smarty->display('sentence.tpl');
}
?>
