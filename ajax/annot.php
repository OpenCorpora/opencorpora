<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_morph_pools.php');
require_once('../lib/lib_achievements.php');

$result['status'] = 1;
try {
    if (!isset($_POST['id']) || (!isset($_POST['answer']) && !isset($_POST['status'])))
        throw new UnexpectedValueException();

    $id = (int)$_POST['id'];
    $answer = isset($_POST['answer']) ? (int)$_POST['answer'] : (int)$_POST['status'];

    if (isset($_POST['mw']) && $_POST['mw'] == 1) {
        require_once('../lib/lib_multiwords.php');
        check_permission(PERM_MULTITOKENS);
        MultiWordTask::register_answer($id, $_SESSION['user_id'], $answer);
    }
    else if (isset($_POST['moder'])) {
        if (isset($_POST['status']))
            $result['status'] = save_moderated_status($id, $answer);
        else
            $result['status'] = save_moderated_answer($id, $answer, (int)$_POST['manual']);
    } else {
        check_logged();
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
