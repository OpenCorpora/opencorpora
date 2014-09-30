<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><lemmata>';
$res = sql_pe("SELECT lemma_id FROM dict_lemmata WHERE lemma_text=?", array($_GET['q']));
foreach ($res as $r) {
    echo '<lemma id="'.$r['lemma_id'].'"/>';
}
echo '</lemmata>';
log_timing(true);
?>
