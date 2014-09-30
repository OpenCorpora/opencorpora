<?php

require_once('../lib/header_ajax.php');
require_once('../lib/lib_generator.php');

if(!is_admin()) {
    return;
}

$published = publish_update();

echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<response>';
echo '<success>' . ($published['success'] ? 'ok' : 'failed') . '</success>';
echo '<output>' . htmlspecialchars($published['output']) . '</output>';
echo '</response>';
log_timing(true);

?>
