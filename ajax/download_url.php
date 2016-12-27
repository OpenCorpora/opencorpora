<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');

try {
    $filename = download_url(POST('url'), POST('force'));
    $result['filename'] = $filename;
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
