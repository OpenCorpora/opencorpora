<?php

require_once('../lib/header.php');
require_once('../lib/lib_tokenizer.php');

if(!is_admin()) {
    return;
}

header('Content-Type: application/x-json; charset=utf-8');

echo json_encode(get_monitor_data($_GET['from'], $_GET['until']));

?>
