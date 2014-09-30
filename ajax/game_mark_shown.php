<?php
require_once('../lib/header.php');
require_once('../lib/lib_awards.php');
header('Content-type: text/xml; charset=utf-8');
$action = $_GET['act'];
$user_id = $_SESSION['user_id'];

$res = true;
try {
    if (!$action || !$user_id)
        throw new UnexpectedValueException();
    switch ($action) {
        case "badge":
            $badge_id = $_GET['badge_id'];
            mark_shown_badge($user_id, $badge_id);
            break;
        case "level":
            $level = $_GET['level'];
            mark_shown_user_level($user_id, $level);
            break;
    }
}
catch (Exception $e) {
    $res = false;
}

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.(int)$res.'"/>';
log_timing(true);
