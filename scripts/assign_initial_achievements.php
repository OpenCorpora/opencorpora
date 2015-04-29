<?php

if (php_sapi_name() == 'cli') {
    set_include_path(get_include_path().PATH_SEPARATOR.__DIR__
        .PATH_SEPARATOR.__DIR__.'/..');
    require_once("lib/header.php");

    $_SESSION['debug_mode'] = true;
    // calculating achievements & levels

    $bobr_levels = array(); // level => number of people
    $chameleon_levels = array();
    $dog_levels = array();
    $pooltypes = array();
    $maxdoge = 0;
    $maxdoge_user = 0;
    foreach (range(0, 4000) as $user_id) {
        // bobr
        $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt FROM morph_annot_instances WHERE user_id = $user_id AND answer > 0"));

        $count = $r['cnt'];
        $level = 0;
        $progress = 0;
        $grades = explode(',', $config['achievements']['bobr']);
        foreach ($grades as $level0 => $COUNT) {
            if ($count > $COUNT) $level++;

            if (isset($grades[$level0 + 1])
                && $count < $grades[$level0 + 1]) {

                $progress = ceil(
                    ($count - $COUNT) * 100 / ($grades[$level0 + 1] - $COUNT)
                );
                break;
            }
        }

        if ($progress < 0) $progress = 0;
        if ($progress > 100) $progress = 100;

        if (isset($bobr_levels[$level])) $bobr_levels[$level]++;
        else $bobr_levels[$level] = 1;
        $level > 20 && $level = 20;

        if ($level > 0) {
        	sql_pe("INSERT INTO user_achievements
        		(user_id, achievement_type, level, progress, seen)
        		VALUES(:user, :type, :level, :progress, 0)
        		ON DUPLICATE KEY UPDATE
                level=VALUES(level), progress = VALUES(progress),
                seen=0",

        		array('user' => $user_id, 'type' => 'bobr',
        			 'level' => $level, 'progress' => $progress));
        }

        ///////////////////
        $res = sql_query("
            SELECT COUNT(instance_id)
            FROM morph_annot_instances
            LEFT JOIN morph_annot_samples
                USING (sample_id)
            LEFT JOIN morph_annot_pools
                USING (pool_id)
            WHERE user_id = $user_id
                AND answer > 0
            GROUP BY pool_type
            ORDER BY COUNT(instance_id) DESC
        ");

        $cnt = array();
        while ($r = sql_fetch_array($res))
            $cnt[] = $r[0];

        $clevels = explode(',', $config['achievements']['chameleon']);
        $clevels = array_map(function($e) {
            return explode(':', $e);
        }, $clevels);

        $level = 0;
        $progress = 0;
        if (!empty($cnt)) {

            $pooltypes[count($cnt)] += 1;

            foreach ($clevels as $level0 => $params) {
                list($types, $min) = $params;
                if (count($cnt) < $types) break;
                foreach ($cnt as $num) {
                    if ($num < $min) break 2;
                }
                $level++;
            }

            if ($level && isset($clevels[$level])) {
            // means that next level can be achieved)
            list($types, $min) = $clevels[$level];
            $has_to_do = $min * $types;
            $did = array_sum(array_slice($cnt, 0, $types));
            $base_for_level = $clevels[$level - 1][0] * $clevels[$level - 1][1];
            $progress = ceil(($did - $base_for_level) * 100 / ($has_to_do - $base_for_level));
            }
        }

        if ($progress < 0) $progress = 0;
        if ($progress > 100) $progress = 100;

        if (isset($chameleon_levels[$level])) $chameleon_levels[$level]++;
        else $chameleon_levels[$level] = 1;
        $level > 20 && $level = 20;
        if ($level > 0) {
        	sql_pe("INSERT INTO user_achievements
        		(user_id, achievement_type, level, progress, seen)
        		VALUES(:user, :type, :level, :progress, 0)
        		ON DUPLICATE KEY UPDATE level=VALUES(level), seen=0",

        		array('user' => $user_id, 'type' => 'chameleon',
        			 'level' => $level, 'progress' => $progress));
        }
		////////////////////////
        // doge
        $res = sql_query("
	        SELECT MONTH(FROM_UNIXTIME(timestamp)) AS month,
	               YEAR(FROM_UNIXTIME(timestamp)) AS year

	        FROM morph_annot_click_log
	        WHERE user_id = $user_id
	            AND clck_type < 10
	        GROUP BY year, month
	        HAVING COUNT(*) >= 50
            AND NOT (month = MONTH(NOW()) AND year = YEAR(NOW()))
	        ORDER BY year, month
	    ");
        $level = 0;
        $progress = 0;
        while ($r = sql_fetch_array($res))
            $level++;

        $res = sql_pe("
                SELECT MONTH(FROM_UNIXTIME(timestamp)) AS month,
                   YEAR(FROM_UNIXTIME(timestamp)) AS year, COUNT(*) as count

                FROM morph_annot_click_log
                WHERE user_id = ?
                    AND clck_type < 10
                GROUP BY year, month
                HAVING month = MONTH(NOW()) AND year = YEAR(NOW())
                ORDER BY year, month", array($user_id));

            $res = $res[0];
            $progress = ceil($res['count'] * 100 / 50);
            if ($progress > 100) $progress = 100;

        if (isset($dog_levels[$level])) $dog_levels[$level]++;
        else $dog_levels[$level] = 1;
        $level > 20 && $level = 20;

        if ($level > 0) {
        	sql_pe("INSERT INTO user_achievements
        		(user_id, achievement_type, level, progress, seen)
        		VALUES(:user, :type, :level, :progress, 0)
        		ON DUPLICATE KEY UPDATE level=VALUES(level), seen=0",

        		array('user' => $user_id, 'type' => 'dog',
        			 'level' => $level, 'progress' => $progress));
        }
    }

    // aist
    sql_pe("insert into user_achievements (user_id, achievement_type, level, progress, seen)
select user_id, 'aist', 1, 0, 0 from users;", array());

    // fish
    sql_pe("insert into user_achievements (user_id, achievement_type, level, progress, seen)
select user_id, 'fish', 1, 0, 0 from users where user_team > 0;", array());

}