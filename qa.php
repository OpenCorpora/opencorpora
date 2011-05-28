<?php
require('lib/header.php');
require('lib/lib_qa.php');
if (is_admin() || user_has_permission('perm_adder')) {
    $smarty->assign('items', get_page_tok_strange());
    $smarty->display('qa/tokenizer.tpl');
} else {
    show_error($config['msg_notadmin']);
}
?>
