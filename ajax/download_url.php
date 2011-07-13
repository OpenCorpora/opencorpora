<?php
require_once('../lib/header.php');
require_once('../lib/lib_books.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if ($filename = download_url($_GET['url'])) {
    echo '<response ok="1" filename="'.$filename.'"/>';
} else {
    echo '<response ok="0"/>';
}
?>
