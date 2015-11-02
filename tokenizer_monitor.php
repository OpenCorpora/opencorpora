<?php

require_once('lib/header.php');

check_permission(PERM_ADMIN);
$smarty->display('tokenizer_monitor.tpl');
?>
