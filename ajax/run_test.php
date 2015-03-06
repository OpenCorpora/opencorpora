<?php

require_once('../lib/header_ajax.php');
require_once('../lib/lib_generator.php');

if(!is_admin()) {
    return;
}

$res = run_test();
$result['error'] = int(!$res['success']);
$result['output'] = htmlspecialchars($res['output']);

log_timing(true);
die(json_encode($result));
?>
