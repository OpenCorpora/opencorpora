<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_annot.php');

if (!is_logged()) {
    return;
}
if (isset($_POST['moder']) && !user_has_permission('perm_check_morph')) {
    return;
}

$result['status'] = 1;
try {
    if (!isset($_POST['id']) || (!isset($_POST['answer']) && !isset($_POST['status'])))
        throw new UnexpectedValueException();

    $id = (int)$_POST['id'];
    $answer = isset($_POST['answer']) ? (int)$_POST['answer'] : (int)$_POST['status'];

    if (isset($_POST['moder'])) {
        if (isset($_POST['status']))
            $result['status'] = save_moderated_status($id, $answer);
        else
            $result['status'] = save_moderated_answer($id, $answer, (int)$_POST['manual']);
    } else {
        update_annot_instance($id, $answer);
    }
}
catch (Exception $e) {
    $result['status'] = 0;
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
