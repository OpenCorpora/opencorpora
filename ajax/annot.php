<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_annot.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if (!is_logged()) {
    return;
}
if (isset($_GET['moder']) && !user_has_permission('perm_check_morph')) {
    return;
}

if (!isset($_GET['id']) || (!isset($_GET['answer']) && !isset($_GET['status']))) {
    echo '<result ok="0"/>';
    return;
}

$id = (int)$_GET['id'];
$answer = isset($_GET['answer']) ? (int)$_GET['answer'] : (int)$_GET['status'];

if (isset($_GET['moder'])) {
    if (isset($_GET['status']))
        echo '<result ok="'.(int)save_moderated_status($id, $answer).'"/>';
    else
        echo '<result ok="'.(int)save_moderated_answer($id, $answer, (int)$_GET['manual']).'"/>';
} else {
    echo '<result ok="'.(int)update_annot_instance($id, $answer).'"/>';
}
?>
