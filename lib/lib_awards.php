<?php
// on/off
function game_is_on() {
    return $_SESSION['show_game'] > 0;
}
function turn_game_on($user_id) {
    sql_query("UPDATE users SET show_game=1 WHERE user_id=$user_id LIMIT 1");
}
function turn_game_off($user_id) {
    sql_query("UPDATE users SET show_game=0 WHERE user_id=$user_id LIMIT 1");
}

// rating and level
function update_user_rating($user_id, $pool_id, $is_skip, $previous_answer) {
    // increase or decrease rating depending on the answer
    // (or do nothing)

    if (($previous_answer && !$is_skip) ||
        (!$previous_answer && $is_skip))
        return true;

    $r = sql_fetch_array(sql_query("
        SELECT rating_weight
        FROM morph_annot_pools p
        JOIN morph_annot_pool_types t
            ON (p.pool_type = t.type_id)
        WHERE pool_id = $pool_id
        LIMIT 1
    "));

    $weight = $r['rating_weight'];
    
    if ($is_skip)
        add_user_rating($user_id, $pool_id, -$weight);
    add_user_rating($user_id, $pool_id, $weight);
}
function add_user_rating($user_id, $pool_id, $weight) {
    sql_begin();

    sql_query("UPDATE users SET user_rating10 = user_rating10 + $weight WHERE user_id=$user_id LIMIT 1");
    sql_query("INSERT INTO user_rating_log VALUES($user_id, $weight, $pool_id, ".time().")");
    sql_commit();
}
function get_user_rating($user_id) {
    $r = sql_fetch_array(sql_query("SELECT user_level, user_rating10 FROM users WHERE user_id=$user_id LIMIT 1"));

    $cur_points = floor($r['user_rating10'] / 10);
    $cur_level = $r['user_level'];
    $cur_level_points = get_rating4level($cur_level);
    $next_level_points = get_rating4level($cur_level + 1);
    return array(
        'total' => $cur_points,
        'current' => ($cur_points - $cur_level_points),
        'remaining_points' => ($next_level_points - $cur_points),
        'remaining_percent' => ceil(($next_level_points - $cur_points) / ($next_level_points - $cur_level_points) * 100)
    );
}
function update_user_level($new_level) {
    if (!$new_level)
        throw new Exception();
    sql_query("UPDATE users SET user_level = $new_level WHERE user_id=".$_SESSION['user_id']." LIMIT 1");
    $_SESSION['user_level'] = $new_level;
}
function mark_shown_user_level($user_id, $level) {
    if (!$user_id || $level < 0)
        throw new UnexpectedValueException();

    $r = sql_fetch_array(sql_query("SELECT user_level, user_shown_level FROM users WHERE user_id=$user_id LIMIT 1"));
    if (
        $r['user_level'] == $r['user_shown_level'] ||
        $level <= $r['user_shown_level'] ||
        $level > $r['user_level']
    )
        throw new Exception();
    sql_query("UPDATE users SET user_shown_level = $level WHERE user_id = $user_id LIMIT 1");
}
function get_rating4level($level) {
    if ($level < 2)
        return 0;
    if ($level == 2)
        return 100;
    return get_rating4level($level - 1) + get_rating4level_aux($level);
}
function get_rating4level_aux($level) {
    if ($level == 2)
        return 100;
    return get_rating4level_aux($level - 1) + 50 * ($level - 1);
}
function get_user_level($user_id) {
    $r = sql_fetch_array(sql_query("SELECT user_level FROM users WHERE user_id=$user_id LIMIT 1"));
    return $r['user_level'];
}
function check_user_level($user_id) {
    if (!$user_id)
        throw new UnexpectedValueException();
    $r = sql_fetch_array(sql_query("SELECT user_rating10, user_level, user_shown_level FROM users WHERE user_id=$user_id LIMIT 1"));
    $next_level = $r['user_level'];
    if (!$next_level)
        throw new Exception();
    $last_shown_level = $r['user_shown_level'];

    if ($next_level > $last_shown_level)
        return $next_level;

    while (true) {
        $points_for_next_level = get_rating4level($next_level);

        if (
            floor($r['user_rating10'] / 10) >= $points_for_next_level &&
            check_badges4level($user_id, $next_level)
        ) {
            $next_level++;
            continue;
        }
        break;
    }
    $next_level--;
    
    if ($next_level == $r['user_level'])
        return 0;
    return $next_level;
}
// badges
function get_user_badges($user_id, $only_shown=true) {
    $only_shown_str = $only_shown ? "AND shown > 0" : '';
    $out = array();
    $res = sql_pe("
        SELECT t.badge_id, t.badge_name, t.badge_descr, t.badge_image, b.shown
        FROM user_badges b
        LEFT JOIN user_badges_types t USING (badge_id)
        WHERE user_id=? $only_shown_str
        AND badge_id IN (
            SELECT MAX(badge_id)
            FROM user_badges
            LEFT JOIN user_badges_types USING (badge_id)
            WHERE user_id=? $only_shown_str
            GROUP BY badge_group
        )
    ", array($user_id, $user_id));
    foreach ($res as $r) {
        $out[] = array(
            'id' => $r['badge_id'],
            'name' => $r['badge_name'],
            'description' => $r['badge_descr'],
            'image' => $r['badge_image'],
            'shown_time' => $r['shown']
        );
    }
    return $out;
}
function check_badges4level($user_id, $level) {
    return true;
}
function mark_shown_badge($user_id, $badge_id) {
    if (!$user_id || !$badge_id)
        throw new UnexpectedValueException();
    sql_query("UPDATE user_badges SET shown=".time()." WHERE user_id=$user_id AND badge_id=$badge_id LIMIT 1");
}
function check_user_badges($user_id) {
    $res = sql_query("SELECT badge_id FROM user_badges WHERE user_id=$user_id AND shown=0 ORDER BY badge_id LIMIT 1");
    if (sql_num_rows($res) > 0) {
        $r = sql_fetch_array($res);
        return get_badge($r['badge_id']);
    }
    $ch = check_user_simple_badges($user_id);
    if ($ch)
        return $ch;
    $ch = check_user_diversity_badges($user_id);
    if ($ch)
        return $ch;
    return check_user_sticking_badges($user_id);
}
function get_user_max_badge_level($user_id, $group_id) {
    $res = sql_query("
        SELECT MAX(badge_id)
        FROM user_badges
        LEFT JOIN user_badges_types USING (badge_id)
        WHERE user_id = $user_id
        AND badge_group = $group_id
    ");
    $r = sql_fetch_array($res);
    $max = $r[0];
    if ($max == 0)
        return 0;

    $res = sql_query("
        SELECT badge_id
        FROM user_badges_types
        WHERE badge_group=$group_id
        ORDER BY badge_id
    ");
    $level = 1;
    while ($r = sql_fetch_array($res))
        if ($r[0] == $max)
            return $level;
        else
            ++$level;
    return 0;
}
function check_user_simple_badges($user_id) {
    global $config;

    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt FROM morph_annot_instances WHERE user_id = $user_id AND answer > 0"));
    $count = $r['cnt'];
    $max_badge = get_user_max_badge_level($user_id, 1);

    $thresholds = explode(',', $config['badges']['simple']);
    foreach ($thresholds as $i => $thr) {
        if ($max_badge > $i)
            continue;
        if ($count < $thr)
            break;
        // user should get a badge!
        $badge_level = $i + 1;
        give_badge($user_id, 1, $badge_level);
        return get_badge_by_group(1, $badge_level);
    }
}
function check_user_diversity_badges($user_id) {
    // done at least X samples in each of Y pool types
    global $config;

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

    $thresholds = explode(',', $config['badges']['diversity']);
    $thresholds = array_map('explode', array_fill(0, sizeof($thresholds), ':'), $thresholds);
    $max_badge = get_user_max_badge_level($user_id, 2);
    foreach ($thresholds as $i => $thr) {
        if ($max_badge > $i)
            continue;
        if (sizeof($cnt) < $thr[$i][0] || $cnt[$i] < $thr[$i][1])
            break;
        // user should get a badge
        $badge_level = $i + 1;
        give_badge($user_id, 2, $badge_level);
        return get_badge_by_group(2, $badge_level);
    }
}
function check_user_sticking_badges($user_id) {
    // parameters are:
    // at least 10 answers within a day for M days
    // at least 50 answers within a week for N weeks
    global $config;

    // days
    $res = sql_query("
        SELECT TO_DAYS(FROM_UNIXTIME(timestamp)) AS day
        FROM morph_annot_click_log
        WHERE user_id = $user_id
            AND clck_type < 10
        GROUP BY TO_DAYS(FROM_UNIXTIME(timestamp))
        HAVING COUNT(*) >= 10
        ORDER BY day
    ");
    $max_days = 1;
    $cur_days = 1;
    $last_day = 0;
    while ($r = sql_fetch_array($res)) {
        if ($r[0] == $last_day + 1) {
            $cur_days++;
            if ($cur_days > $max_days)
                $max_days = $cur_days;
        }
        else
            $cur_days = 1;
        $last_day = $r[0];
    }

    // weeks
    // -2 comes from 1 Jan 0000 being not a Monday
    $res = sql_query("
        SELECT FLOOR((TO_DAYS(FROM_UNIXTIME(timestamp)) - 2) / 7) AS week
        FROM morph_annot_click_log
        WHERE user_id = $user_id
            AND clck_type < 10
        GROUP BY FLOOR((TO_DAYS(FROM_UNIXTIME(timestamp)) - 2) / 7)
        HAVING COUNT(*) >= 50
        ORDER BY week
    ");
    $max_weeks = 1;
    $cur_weeks = 1;
    $last_week = 0;
    while ($r = sql_fetch_array($res)) {
        if ($r[0] == $last_week + 1) {
            $cur_weeks++;
            if ($cur_weeks > $max_weeks)
                $max_weeks = $cur_weeks;
        }
        else
            $cur_weeks = 1;
        $last_week = $r[0];
    }

    $thresholds = explode(',', $config['badges']['returns']);
    $thresholds = array_map('explode', array_fill(0, sizeof($thresholds), ':'), $thresholds);

    $max_badge = get_user_max_badge_level($user_id, 3);
    foreach ($thresholds as $i => $thr) {
        if ($max_badge > $i)
            continue;
        if ($thr[1] != 'd' && $thr[1] != 'w')
            throw new Exception();
        if (
            ($thr[1] == 'd' && $max_days < $thr[0]) ||
            ($thr[1] == 'w' && $max_weeks < $thr[0])
        )
            break;
        // user should get a badge
        $badge_level = $i + 1;
        give_badge($user_id, 3, $badge_level);
        return get_badge_by_group(3, $badge_level);
    }
}
function check_user_date_badges($user_id) {
    // at least N samples with error rate at most E on day(s) X
}
function give_badge($user_id, $group_id, $badge_level) {
    sql_query("INSERT INTO user_badges VALUES($user_id, (SELECT badge_id FROM user_badges_types WHERE badge_group=$group_id LIMIT ".($badge_level-1).", 1), 0)");
}
function get_badge($badge_id) {
    if (!$badge_id)
        throw new UnexpectedValueException();

    $r = sql_fetch_array(sql_query("SELECT badge_name, badge_descr, badge_image FROM user_badges_types WHERE badge_id=$badge_id LIMIT 1"));
    return array (
        'id' => $badge_id,
        'name' => $r['badge_name'],
        'description' => $r['badge_descr'],
        'image' => $r['badge_image']
    );
}
function get_badge_by_group($group_id, $level) {
    $r = sql_fetch_array(sql_query("
        SELECT badge_id
        FROM user_badges_types
        WHERE badge_group = $group_id
        ORDER BY badge_id
        LIMIT ".($level - 1).", 1
    "));
    return get_badge($r[0]);
}

// admin
function get_badges_info() {
    $res = sql_query("
        SELECT badge_id, badge_name, badge_descr, badge_image, badge_group
        FROM user_badges_types
        ORDER BY badge_group, badge_id
    ");
    $out = array();

    while ($r = sql_fetch_array($res))
        $out[] = array(
            'id' => $r['badge_id'],
            'name' => $r['badge_name'],
            'description' => $r['badge_descr'],
            'image' => $r['badge_image'],
            'group' => $r['badge_group']
        );
    return $out;
}
function save_badges_info($post) {
    sql_begin();
    $new_badge = sql_prepare("
        INSERT INTO user_badges_types
        VALUES (?, ?, ?, ?, ?)
    ");
    $update_badge = sql_prepare("
        UPDATE user_badges_types
        SET badge_name=?,
        badge_image=?,
        badge_descr=?,
        badge_group=?
        WHERE badge_id=?
        LIMIT 1
    ");
    foreach ($post['badge_name'] as $id => $name) {
        $id = $id;
        $name = trim($name);
        $image = trim($post['badge_image'][$id]);
        $descr = trim($post['badge_descr'][$id]);
        $group = trim($post['badge_group'][$id]);
        if ($id == -1 && $name) {
            $r = sql_fetch_array(sql_query("SELECT MAX(badge_id) FROM user_badges_types"));
            sql_execute($new_badge, array($r[0]+1, $name, $descr, $image, $group));
        }
        elseif ($id > 0)
            sql_execute($update_badge, array($name, $image, $descr, $group, $id));
        else
            throw new UnexpectedValueException();
    }
    sql_commit();
}
?>
