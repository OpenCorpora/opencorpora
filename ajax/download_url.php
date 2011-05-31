<?php
require_once('../lib/header.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if (!isset($_GET['url'])) {
    echo '<response ok="0"/>';
    return;
}

//check if it has been already downloaded
$res = sql_query("SELECT url FROM downloaded_urls WHERE url='".mysql_real_escape_string($_GET['url'])."' LIMIT 1");
if (sql_num_rows($res) > 0) {
    echo '<response ok="0"/>';
    return;
}

//downloading
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $_GET['url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'OpenCorpora.org bot');
$contents = curl_exec($ch);
curl_close($ch);

//writing to disk
$filename = uniqid('', 1);
$res = file_put_contents("../files/saved/$filename.html", $contents);
if (!$res) {
    echo '<response ok="0"/>';
    return;
}

if (sql_query("INSERT INTO downloaded_urls VALUES('".mysql_real_escape_string($_GET['url'])."', '$filename')")) {
    echo '<response ok="1" filename="'.$filename.'"/>';
} else {
    echo '<response ok="0"/>';
}
?>
