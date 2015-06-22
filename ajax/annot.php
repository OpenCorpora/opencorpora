<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_morph_pools.php');
require_once('../lib/lib_achievements.php');

if (!is_logged()) {
    return;
}
if (isset($_POST['moder']) && !user_has_permission(PERM_MORPH_MODER)) {
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

        $am = new AchievementsManager((int)$_SESSION['user_id']);
        $am->emit(EventTypes::TASK_DONE);
    }
}
catch (Exception $e) {
    $result['status'] = $e->getMessage();
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
