<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_achievements.php');

try {
    check_logged();
    $am = new AchievementsManager($_SESSION['user_id']);
    $am->set_all_seen();
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
