<?php
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response readonly="'.(file_exists('/var/lock/oc_readonly.lock') ? 1 : 0).'"/>';
?>
