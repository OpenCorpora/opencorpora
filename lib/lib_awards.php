<?php
require_once('lib_users.php');
// on/off
function game_is_on() {
    return OPTION(OPT_GAME_ON) == 1;
}

// rating and level
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

    $res = sql_pe("SELECT user_level, user_shown_level FROM users WHERE user_id=? LIMIT 1", array($user_id));
    $r = $res[0];
    if (
        $r['user_level'] == $r['user_shown_level'] ||
        $level <= $r['user_shown_level'] ||
        $level > $r['user_level']
    )
        throw new Exception();
    sql_pe("UPDATE users SET user_shown_level = ? WHERE user_id = ? LIMIT 1", array($level, $user_id));
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

        if (floor($r['user_rating10'] / 10) >= $points_for_next_level) {
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
?>
