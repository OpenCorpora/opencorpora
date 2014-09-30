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

$result = 1;
try {
    if (!isset($_GET['id']) || (!isset($_GET['answer']) && !isset($_GET['status'])))
        throw new UnexpectedValueException();

    $id = (int)$_GET['id'];
    $answer = isset($_GET['answer']) ? (int)$_GET['answer'] : (int)$_GET['status'];

    if (isset($_GET['moder'])) {
        if (isset($_GET['status']))
            $result = save_moderated_status($id, $answer);
        else
            $result = save_moderated_answer($id, $answer, (int)$_GET['manual']);
    } else {
        update_annot_instance($id, $answer);
    }
}
catch (Exception $e) {
    $result = 0;
}
log_timing(true);
echo '<result ok="'.$result.'"/>';
?>
