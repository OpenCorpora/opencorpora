<?php
require_once('../lib/header_ajax.php');
$type = POST('type');
$id = (int)POST('id');
$value = POST('value');

try {
    switch($type) {
        case 'source':
            check_permission(PERM_ADDER);
            if ($value === '0' || $value === '1')
                sql_query("INSERT INTO sources_status VALUES('$id', '".$_SESSION['user_id']."', '$value', '".time()."')");
            break;
        default:
            throw new Exception();
    }
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
