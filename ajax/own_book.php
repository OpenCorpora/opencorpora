<?php
require_once('../lib/header.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if (!user_has_permission('perm_adder')) {
    return;
}

if (!isset($_GET['sid']) || !isset($_GET['status'])) {
    echo '<result ok="0"/>';
    return;
}

$sid = (int)$_GET['sid'];
$status = (int)$_GET['status'];

if ($status == 0) {
    $r = sql_fetch_array(sql_query("SELECT user_id FROM sources WHERE source_id=$sid LIMIT 1"));
    if ($r['user_id'] != $_SESSION['user_id']) {
        echo '<result ok="0"/>';
        return;
    }
}

$user_id = $status > 0 ? $_SESSION['user_id'] : 0;

if (sql_query("UPDATE sources SET user_id='$user_id' WHERE source_id=$sid LIMIT 1")) {
    echo '<result ok="1"/>';
} else {
    echo '<result ok="0"/>';
}
