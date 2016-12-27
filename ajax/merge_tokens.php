<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');

try {
    merge_tokens_ii(explode(',', POST('ids')));
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
