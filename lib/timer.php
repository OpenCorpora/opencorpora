<?php
function log_timing($is_ajax=false) {
    global $config;
    global $total_time;

    if ($total_time < $config['misc']['timing_log_threshold'])
        return;

    $user_id = 0;
    // todo use is_logged() when it doesn't depend on pending license
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0)
        $user_id = $_SESSION['user_id'];

    $page = $_SERVER['REQUEST_URI'];
    sql_pe(
        "INSERT INTO timing (user_id, page, total_time, is_ajax) VALUES (?, ?, ?, ?)",
        array($user_id, $page, $total_time, $is_ajax ? 1 : 0)
    );
}
?>
