<?php

require_once('../lib/header_ajax.php');
require_once('../lib/lib_achievements.php');

if (!is_logged())
    return;

$am = new AchievementsManager((int)$_SESSION['user_id']);
$am->emit(EventTypes::WANT_MORE);