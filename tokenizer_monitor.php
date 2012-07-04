<?php

require_once('lib/header.php');

if (is_admin()) {
    $smarty->display('tokenizer_monitor.tpl');
}
else {
    show_error($config['msg']['notadmin']);
}
?>
