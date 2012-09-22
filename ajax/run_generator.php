<?php

require_once('../lib/header_ajax.php');
require_once('../lib/lib_generator.php');

if(!is_admin()) {
    return;
}

$result = run_generator();

echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<response>';
echo '<success>' . ($result['success'] ? 'ok' : 'failed') . '</success>';
echo '<output>' . htmlspecialchars($result['output']) . '</output>';
echo '</response>';

?>
