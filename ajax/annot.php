<?php
require_once('../lib/header.php');
require_once('../lib/lib_qa.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if (!is_logged()) {
    return;
}

if (!isset($_GET['id']) || !isset($_GET['answer'])) {
    echo '<result ok="0"/>';
    return;
}

$id = (int)$_GET['id'];
$answer = (int)$_GET['answer'];

echo '<result ok="'.(int)update_annot_instance($id, $answer).'"/>';
?>
