<?php

require_once('../lib/header_ajax.php');
require_once('../lib/lib_generator.php');

if(!is_admin()) {
    return;
}

$published = publish_update();
$result['error'] = int(!$published['success']);
$result['output'] = htmlspecialchars($published['output']);

log_timing(true);
die(json_encode($result));
?>
