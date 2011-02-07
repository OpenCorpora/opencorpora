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
function lc($str) {
    $convert_from = array ('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я');
    $convert_to = array ('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    return str_replace($convert_from, $convert_to, strtolower($str));
}
function show_page($template_name) {
    global $smarty;
    $lang_code = $_SESSION['options'][2];
    if (!$lang_code) $lang_code = 1;

    if ($lang_code == 2 && $smarty->template_exists("english/$template_name")) {
        $smarty->display("english/$template_name");
    }
    elseif ($lang_code ==1 && $smarty->template_exists("russian/$template_name")) {
        $smarty->display("russian/$template_name");
    }
    elseif ($smarty->template_exists($template_name)) {
        $smarty->display($template_name);
    }
    else {
        show_error("Шаблон не найден: $template_name");
    }
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
