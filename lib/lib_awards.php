<?php
// rating and level
function add_user_rating($user_id, $pool_id) {
    global $config;
    $r = sql_fetch_array(sql_query("SELECT grammemes FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    $signature = strtr($r['grammemes'], '&|', '__');
    if (isset($config['pools_complexity'][$signature]))
        $weight = $config['pools_complexity'][$signature];
    else
        $weight = $config['pools_complexity']['default'];

    sql_begin();

    if (sql_query("UPDATE users SET user_rating10 = user_rating10 + $weight WHERE user_id=$user_id LIMIT 1") &&
        sql_query("INSERT INTO user_rating_log VALUES($user_id, $weight, $pool_id, ".time().")")) {
        sql_commit();
        return true;
    }
    return false;
}
function get_user_rating($user_id) {
    $r = sql_fetch_array(sql_query("SELECT user_level, user_rating10 FROM users WHERE user_id=$user_id LIMIT 1"));

    $cur_points = floor($r['user_rating10'] / 10);
    $cur_level = $r['user_level'];
    $cur_level_points = get_rating4level($cur_level);
    $next_level_points = get_rating4level($cur_level + 1);
    $got_level = update_user_level($user_id);
    return array(
        'current' => ($cur_points - $cur_level_points),
        'remaining_points' => ($next_level_points - $cur_points),
        'remaining_percent' => ceil(($next_level_points - $cur_points) / ($next_level_points - $cur_level_points) * 100),
        'got_level' => $got_level
    );
}
function update_user_level($user_id) {
    $r = sql_fetch_array(sql_query("SELECT user_rating10, user_level FROM users WHERE user_id=$user_id LIMIT 1"));
    $next_level = $r['user_level'] + 1;
    $points_for_next_level = get_rating4level($next_level);

    if (floor($r['user_rating10'] / 10) < $points_for_next_level)
        return 0;

    // so rating points are sufficient
    if (check_badges4level($next_level)) {
        if (sql_query("UPDATE users SET user_level = $next_level WHERE user_id=$user_id LIMIT 1")) {
            $_SESSION['user_level'] = $next_level;
            return $next_level;
        }
    }
    return 0;
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
// badges
function get_user_badges($user_id, $only_shown=true) {
    $only_shown_str = $only_shown ? "AND shown > 0" : '';
    $out = array();
    $res = sql_query("SELECT t.badge_id, t.badge_name, t.badge_descr, t.badge_image, b.shown
                        FROM user_badges b
                        LEFT JOIN user_badges_types t USING (badge_id)
                        WHERE user_id=$user_id $only_shown_str");
    while ($r = sql_fetch_array($res)) {
        $out[] = array(
            'id' => $r['badge_id'],
            'name' => $r['badge_name'],
            'description' => $r['badge_descr'],
            'image_name' => $r['badge_image'],
            'shown_time' => $r['shown']
        );
    }
    return $out;
}
function check_badges4level($user_id, $level) {
    return true;
}
function mark_shown_badge($user_id, $badge_id) {
    if (sql_query("UPDATE user_badges SET shown=".time()." WHERE user_id=$user_id AND badge_id=$badge_id LIMIT 1"))
        return true;
    return false;
}
function check_user_simple_badges($user_id) {
    global $config;
    $thresholds = explode(',', $config['badges']['simple']);
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt FROM morph_annot_instances WHERE user_id = $user_id AND answer > 0"));
    $count = $r['cnt'];
    $res = sql_query("SELECT MAX(badge_id) AS max_badge FROM user_badges WHERE user_id = $user_id AND badge_id <= 20");
    if (sql_num_rows($res) == 0)
        $max_badge = 0;
    else {
        $r = sql_fetch_array($res);
        $max_badge = $r['max_badge'];
    }

    foreach ($thresholds as $i => $thr) {
        if ($max_badge > $i)
            continue;
        if ($count < $thr)
            break;
        // user should get a badge!
        $badge_id = $i + 1;
        if (sql_query("INSERT INTO user_badges VALUES($user_id, $badge_id, 0)"))
            return $badge_id;
        break;
    }
    return false;
}
?>
