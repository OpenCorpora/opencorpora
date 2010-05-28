<?php
header('Content-type: text/xml; charset=utf-8');
require_once('../lib/header.php');
require_once('../lib/lib_dict.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response>';
if (isset($_GET['tf_id'])) {
    $tf_id = (int)$_GET['tf_id'];
    $r = sql_fetch_array(sql_query("SELECT tf_text FROM text_forms WHERE tf_id=$tf_id LIMIT 1"));
    echo generate_tf_rev($r['tf_text']);
}
echo '</response>';
?>
