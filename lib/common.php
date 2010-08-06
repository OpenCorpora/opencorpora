<?php
require_once('lib_users.php');

//sql wrappers
function sql_query($q, $debug=1) {
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
function lc($str) {
    $convert_from = array ('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я');
    $convert_to = array ('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    return str_replace($convert_from, $convert_to, strtolower($str));
}
function create_revset() {
    if (sql_query("INSERT INTO `rev_sets` VALUES(NULL, '".time()."', '".$_SESSION['user_id']."')")) {
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
function url2href($str, $target='_blank') {
    return '<a href="'.$str.'" target="$target">'.htmlspecialchars($str).'</a>';
}
function get_common_stats() {
    $stats = array();
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_users FROM `users`"));
    $stats['cnt_users'] = $r['cnt_users'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_books FROM `books`"));
    $stats['cnt_books'] = $r['cnt_books'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_sent FROM `sentences`"));
    $stats['cnt_sent'] = $r['cnt_sent'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_words FROM `text_forms`"));
    $stats['cnt_words'] = $r['cnt_words'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_lemmata FROM `dict_lemmata`"));
    $stats['cnt_lemmata'] = $r['cnt_lemmata'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_forms FROM `form2lemma`"));
    $stats['cnt_forms'] = $r['cnt_forms'];
    return $stats;
}
?>
