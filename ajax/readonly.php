<?php
$result = array('readonly' => file_exists('/var/lock/oc_readonly.lock') ? 1 : 0);
die(json_encode($result));
?>
