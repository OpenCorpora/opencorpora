<?php
require_once('lib_users.php');

//sql wrappers
function sql_query($q, $debug=1, $override_readonly=0) {
    if (file_exists('/var/lock/oc_readonly.lock') && stripos($q, 'select') !== 0 && !$override_readonly) 
        return false;
    $debug = isset($_SESSION['debug_mode']) && $debug;
    if ($debug)
        $time_start = microtime(true);
    $res = mysql_query($q);
    if ($debug) {
        $time = microtime(true)-$time_start;
        printf ("<span class='debug'>SQL: ".htmlspecialchars($q)." # %.4f сек.</span><br/>\n", $time);
        if ($err = mysql_error()) {
            print "<span class='debug_error'>$err</span><br/>\n";
        }
    }
    return $res;
}
function sql_fetch_array($q) {
    return mysql_fetch_array($q);
}
function sql_num_rows($q) {
    return mysql_num_rows($q);
}
function sql_insert_id() {
    return mysql_insert_id();
}
//sql checks
function sql_get_schema() {
    $res = sql_query("SHOW TABLES");
    $out = array();
    while($r = sql_fetch_array($res)) {
        $out[$r[0]] = array();
        $res1 = sql_query("EXPLAIN `$r[0]`");
        while($r1 = sql_fetch_array($res1)) {
            $out[$r[0]][] = strtolower($r1[0]);
        }
    }
    return $out;
}
//other
function translate($params, $content, $smarty, &$repeat) {
    return _($content);
}
function show_error($text = "Произошла ошибка.") {
    global $smarty;
    $smarty->assign('error_text', $text);
    $smarty->display('error.tpl');
}
function create_revset($comment = '') {
    if (sql_query("INSERT INTO `rev_sets` VALUES(NULL, '".time()."', '".$_SESSION['user_id']."', '$comment')")) {
        return sql_insert_id();
    }
    return 0;
}
function typo_spaces($str, $with_tags = 0) {
    if (!$with_tags) {
        $patterns = array(' ,', ' .', '( ', ' )', ' :', ' ;');
        $replacements = array(',', '.', '(', ')', ':', ';');
        return str_replace($patterns, $replacements, $str);
    }
    $patterns = array('/\s(<[^>]+>(?:\.|\,|\)|:|;)<\/[^>]+>)/', '/(<[^>]+>(?:\()<\/[^>]+>)\s/');
    $replacements = array('\1', '\1');
    return preg_replace($patterns, $replacements, $str);
}
function get_common_stats() {
    $stats = array();

    $res = sql_query("SELECT * FROM stats_param WHERE is_active=1");
    while($r = sql_fetch_array($res)) {
        $arr = sql_fetch_array(sql_query("SELECT `timestamp`, param_value FROM stats_values WHERE param_id=".$r['param_id']." ORDER BY `timestamp` DESC LIMIT 1"));
        $stats[$r['param_name']] = array('timestamp' => $arr['timestamp'], 'value' => $arr['param_value']);
    }

    return $stats;
}
function get_downloads_info() {
    $dict = array();
    $stat = stat('files/export/dict/dict.opcorpora.xml.bz2');
    $dict['size'] = sprintf("%.2f", $stat[7] / (1024 * 1024));
    $dict['updated'] = date('d.m.Y H:i \U\T\C', $stat[9]);
    return array('dict'=>$dict);
}
function set_readonly_on() {
    if (!is_admin()) return 0;
    touch_file('/var/lock/oc_readonly.lock');
}
function set_readonly_off() {
    if (!is_admin()) return 0;
    unlink('/var/lock/oc_readonly.lock');
}
function touch_file($path) {
    exec("touch {$path}");
}
?>
