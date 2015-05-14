<?php
require_once('../lib/header.php');
require_once('../lib/lib_achievements.php');

$user_id = $_SESSION['user_id'];

try {
    if (!$user_id)
        throw new UnexpectedValueException();

    $badge_id = $_POST['badge_id'];
    $am = new AchievementsManager($user_id);
    $am->set_all_seen();

    break;
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
