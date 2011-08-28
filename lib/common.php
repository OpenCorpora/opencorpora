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
        printf ("<span class='debug'>SQL: %s # %.4f сек.</span><br/>\n", htmlspecialchars($q), $time);
        if ($err = mysql_error()) {
            print "<span class='debug_error'>$err</span><br/>\n";
        }
    }
    return $res;
}
function sql_fetch_array($q) {
    return mysql_fetch_array($q);
}
function sql_fetch_assoc($q) {
    return mysql_fetch_assoc($q);
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
    if (sql_query("INSERT INTO `rev_sets` VALUES(NULL, '".time()."', '".$_SESSION['user_id']."', '".mysql_real_escape_string($comment)."')")) {
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
    global $config;
    $stats = array();

    $res = sql_query("SELECT * FROM stats_param WHERE is_active=1 AND param_id NOT IN(SELECT DISTINCT param_id FROM user_stats)");
    while($r = sql_fetch_array($res)) {
        $arr = sql_fetch_array(sql_query("SELECT `timestamp`, param_value FROM stats_values WHERE param_id=".$r['param_id']." ORDER BY `timestamp` DESC LIMIT 1"));
        $stats[$r['param_name']] = array('timestamp' => $arr['timestamp'], 'value' => $arr['param_value']);
    }

    foreach(array('total', 'chaskor', 'wikipedia', 'wikinews', 'blogs') as $src) {
        $stats['percent_words'][$src] = floor($stats[$src.'_words']['value'] / $config['goals'][$src.'_words'] * 100);
    }

    //user stats
    $res = sql_query("SELECT timestamp, u.user_name, param_value FROM user_stats s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE param_id=6 ORDER BY param_value DESC");
    while ($r = sql_fetch_array($res)) {
        $stats['added_sentences'][] = array('timestamp' => $r['timestamp'], 'user_name' => $r['user_name'], 'value' => $r['param_value']);
    }
    $res = sql_query("SELECT timestamp, u.user_name, param_value FROM user_stats s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE param_id=7 ORDER BY param_value DESC");
    while ($r = sql_fetch_array($res)) {
        $stats['added_sentences_last_week'][] = array('timestamp' => $r['timestamp'], 'user_name' => $r['user_name'], 'value' => $r['param_value']);
    }

    return $stats;
}
function get_tag_stats() {
    $out = array();
    $res = sql_query("SELECT prefix, value, texts, words FROM tag_stats ORDER BY prefix, texts DESC, words DESC");
    
    while ($r = sql_fetch_array($res)) {
        $out[$r['prefix']][] = array('value' => $r['value'], 'texts' => $r['texts'], 'words' => $r['words']);
    }
    return $out;
}
function get_downloads_info() {
    $dict = array();
    $kb = 1024 * 1024;
    $stat = stat('files/export/dict/dict.opcorpora.xml.bz2');
    $dict['xml']['bz2']['size'] = sprintf("%.2f", $stat[7] / $kb);
    $dict['xml']['updated'] = date('d.m.Y H:i \M\S\K', $stat[9]);
    $stat = stat('files/export/dict/dict.opcorpora.xml.zip');
    $dict['xml']['zip']['size'] = sprintf("%.2f", $stat[7] / $kb);

    $stat = stat('files/export/dict/dict.opcorpora.txt.bz2');
    $dict['txt']['bz2']['size'] = sprintf("%.2f", $stat[7] / $kb);
    $dict['txt']['updated'] = date('d.m.Y H:i \M\S\K', $stat[9]);
    $stat = stat('files/export/dict/dict.opcorpora.txt.zip');
    $dict['txt']['zip']['size'] = sprintf("%.2f", $stat[7] / $kb);

    $annot = array();
    $stat = stat('files/export/annot/annot.opcorpora.xml.bz2');
    $annot['xml']['bz2']['size'] = sprintf("%.2f", $stat[7] / $kb);
    $annot['xml']['updated'] = date('d.m.Y H:i \M\S\K', $stat[9]);
    $stat = stat('files/export/annot/annot.opcorpora.xml.zip');
    $annot['xml']['zip']['size'] = sprintf("%.2f", $stat[7] / $kb);

    return array('dict'=>$dict, 'annot'=>$annot);
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
