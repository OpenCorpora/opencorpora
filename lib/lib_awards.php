<?php
// rating
function add_rating($user_id, $pool_id) {
    // TODO: update user level if needed
    $r = sql_fetch_array(sql_query("SELECT grammemes FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    $signature = strtr($r['grammemes'], '&|', '__');
    if (isset($config['pools_complexity'][$signature]))
        $weight = $config['pools_complexity'][$signature];
    else
        $weight = $config['pools_complexity']['default'];

    sql_begin();

    if (sql_query("UPDATE users SET rating10 = rating10 + $weight WHERE user_id=$user_id LIMIT 1") &&
        sql_query("INSERT INTO user_rating_log VALUES($user_id, $weight, $pool_id, ".time().")")) {
        sql_commit();
        return true;
    }
    return false;
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
function mark_shown_badge($user_id, $badge_id) {
    // TODO: update user level if needed
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
