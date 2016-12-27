<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_morph_pools.php');

try {
    log_click(POST('id'), POST('type'));
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
