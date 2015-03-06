<?php
require_once('../lib/header.php');
require_once('../lib/lib_awards.php');

$action = $_POST['act'];
$user_id = $_SESSION['user_id'];

try {
    if (!$action || !$user_id)
        throw new UnexpectedValueException();
    switch ($action) {
        case "badge":
            $badge_id = $_POST['badge_id'];
            mark_shown_badge($user_id, $badge_id);
            break;
        case "level":
            $level = $_POST['level'];
            mark_shown_user_level($user_id, $level);
            break;
    }
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
