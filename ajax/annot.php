<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_morph_pools.php');
require_once('../lib/lib_achievements.php');

$result['status'] = 1;
try {
    $id = (int)POST('id');
    $answer = POST('answer', false);
    if ($answer === false)
        $answer = POST('status');
    $answer = (int)$answer;

    if (POST('mw', 0)) {
        require_once('../lib/lib_multiwords.php');
        check_permission(PERM_MULTITOKENS);
        MultiWordTask::register_answer($id, $_SESSION['user_id'], $answer);
    }
    else if (POST('moder', 0)) {
        if (POST('status', false) !== false)
            $result['status'] = save_moderated_status($id, $answer);
        else
            $result['status'] = save_moderated_answer($id, $answer, (int)POST('manual'));
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
