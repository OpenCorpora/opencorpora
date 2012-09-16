<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if ($filename = download_url($_GET['url'], isset($_GET['force']))) {
    echo '<response ok="1" filename="'.$filename.'"/>';
} else {
    echo '<response ok="0"/>';
}
?>
