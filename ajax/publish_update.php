<?php

require_once('../lib/header.php');
require_once('../lib/lib_generator.php');

if(!is_admin()) {
    return;
}

header('Content-Type: text/xml; charset=utf-8');

$published = publish_update();

echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<response>';
echo '<success>' . ($published ? 'ok' : 'failed') . '</success>';
echo '<output>' . ($published ? 'Update finished' : 'Update failed') . '</output>';
echo '</response>';

?>
