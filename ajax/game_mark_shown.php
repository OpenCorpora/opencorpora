<?php
require_once('../lib/header.php');
require_once('../lib/lib_awards.php');
header('Content-type: text/xml; charset=utf-8');
$action = $_GET['act'];
$user_id = $_SESSION['user_id'];
if(!$action || !$user_id) {
    die('Wrong request');
}
switch($action) {
    case "badge":
        $badge_id = (int)$_GET['badge_id'];
        if(!$badge_id) {
            die('Wrong request');
        }
        $res = mark_shown_badge($user_id,$badge_id);
        break;
    case "level":
        $level = (int)$_GET['level'];
        if(!$level) {
            die('Wrong request');
        }
        $res = mark_shown_user_level($user_id,$level);
        break;
}
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><result ok="'.(int)$res.'"/>';
