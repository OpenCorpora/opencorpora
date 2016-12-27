<?php

require_once('../lib/header_ajax.php');
require_once('../lib/lib_tokenizer.php');

if (!is_admin()) {
    return;
}

$result['data'] = get_monitor_data(POST('from'), POST('until'));
log_timing(true);
die(json_encode($result));
?>
