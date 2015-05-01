<?php

if (php_sapi_name() == 'cli') {
    set_include_path(get_include_path().PATH_SEPARATOR.__DIR__
        .PATH_SEPARATOR.__DIR__.'/..');
    require_once("lib/header.php");
    require_once("lib/lib_achievements.php");

    if (date("j") != 1) die("Today is not the first day of the month, finishing.\n");

    print "Pinging dog achievement recalculation.. \n";
    // get all users which registered the same day as today
    $users = sql_pe("SELECT user_id FROM users
    	WHERE DATE(FROM_UNIXTIME(user_reg)) < DATE_SUB(DATE(NOW()), INTERVAL 15 DAY)
    	   ", array());

    print "Month(s) (or at least 15 days) since registration passed for ".count($users)." user(s)\n";
    foreach ($users as $u) {
    	$am = new AchievementsManager((int)$u['user_id']);
    	$am->emit(EventTypes::MONTH_PASSED);
    }
    print "Achievements pinged, finishing\n";
 }