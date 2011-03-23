<?php
header('Content-type: text/xml; charset=utf-8');
require_once('../lib/header.php');
require_once('../lib/lib_dict.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><lemmata>';
$res = sql_query("SELECT lemma_id FROM dict_lemmata WHERE lemma_text='".mysql_real_escape_string($_GET['q'])."'");
while($r = sql_fetch_array($res)) {
    echo '<lemma id="'.$r['lemma_id'].'"/>';
}
echo '</lemmata>';
?>
