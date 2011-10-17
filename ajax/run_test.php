<?php

require_once('../lib/header.php');
require_once('../lib/lib_generator.php');

if(!is_admin()) {
    return;
}

header('Conten-Type: text/xml; charset=utf-8');

$result = run_test();

echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<response>';
echo '<success>' . ($result['success'] ? 'ok' : 'failed') . '</success>';
echo '<output>' . htmlspecialchars($result['output']) . '</output>';
echo '</response>';

?>
