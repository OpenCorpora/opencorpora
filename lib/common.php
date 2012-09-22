<?php
require_once('lib_users.php');

//sql wrappers
function sql_query($q, $debug=1, $override_readonly=0) {
    global $total_time;
    global $total_queries;
    if (file_exists('/var/lock/oc_readonly.lock') && stripos(trim($q), 'select') !== 0 && !$override_readonly)
        return false;
    $debug = isset($_SESSION['debug_mode']) && $debug;
    if ($debug) {
        $time_start = microtime(true);
        $q = str_ireplace('select ', 'SELECT SQL_NO_CACHE ', $q);
    }
    $res = mysql_query($q);
    if ($debug) {
        $time = microtime(true)-$time_start;
        $total_time += $time;
        $total_queries++;
        printf("<table class='debug' width='100%%'><tr><td valign='top' width='20'>%d<td>SQL: %s</td><td width='100'>%.4f сек.</td><td width='100'>%.4f сек.</td></tr></table>\n", $total_queries, htmlspecialchars($q), $time, $total_time);
        if ($err = mysql_error()) {
            print "<table class='debug_error' width='100%'><tr><td colspan='3'>".htmlspecialchars($err)."</td></tr></table>\n";
        }
    }
    return $res;
}
function sql_fetch_array($q) {
    if (!$q) return false;
    return mysql_fetch_array($q);
}
function sql_fetch_assoc($q) {
    if (!$q) return false;
    return mysql_fetch_assoc($q);
}
function sql_num_rows($q) {
    if (!$q) return false;
    return mysql_num_rows($q);
}
function sql_insert_id() {
    return mysql_insert_id();
}
function sql_begin() {
    global $transaction_counter;
    global $nested_transaction_counter;
    if (!$transaction_counter) {
        sql_query("START TRANSACTION", 1, 1);
        ++$transaction_counter;
    } else {
        ++$nested_transaction_counter;
    }
}
function sql_commit() {
    global $transaction_counter;
    global $nested_transaction_counter;
    if ($nested_transaction_counter) {
        --$nested_transaction_counter;
    } else {
        sql_query("COMMIT", 1, 1);
        --$transaction_counter;
    }
}
//sql checks
function sql_get_schema() {
    $res = sql_query("SHOW TABLES");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[$r[0]] = array();
        $res1 = sql_query("EXPLAIN `$r[0]`");
        while ($r1 = sql_fetch_array($res1)) {
            $out[$r[0]][] = strtolower($r1[0]);
        }
    }
    return $out;
}
//other
function translate($params, $content, $smarty, &$repeat) {
    return _($content);
}
function is_cached($template_name, $cache_id = false) {
    if (isset($_SESSION['debug_mode'])) return false;
    global $smarty;
    return $cache_id ? $smarty->isCached($template_name, $cache_id)
                     : $smarty->isCached($template_name);
}
function show_error($text = "Произошла ошибка.") {
    global $smarty;
    $smarty->assign('error_text', $text);
    $smarty->display('error.tpl');
}
function create_revset($comment = '') {
    if (!isset($_SESSION['user_id']) || !$_SESSION['user_id'])
        return 0;
    if (sql_query("INSERT INTO `rev_sets` VALUES(NULL, '".time()."', '".(int)$_SESSION['user_id']."', '".mysql_real_escape_string($comment)."')")) {
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
function get_wiki_page($title) {
    return htmlspecialchars_decode(fetch_wiki_page($title));
}
function fetch_wiki_page($title) {
    $url = "http://localhost/w/api.php?action=query&prop=revisions&titles=$title&rvprop=content&rvparse=1&format=xml";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($ch);
    curl_close($ch);
    return $contents;
}
function get_common_stats() {
    global $config;
    $stats = array();

    $res = sql_query("SELECT * FROM stats_param WHERE is_active=1 AND param_id NOT IN(SELECT DISTINCT param_id FROM user_stats)");
    while ($r = sql_fetch_array($res)) {
        $arr = sql_fetch_array(sql_query("SELECT param_value FROM stats_values WHERE param_id=".$r['param_id']." ORDER BY `timestamp` DESC LIMIT 1"));
        $stats[$r['param_name']] = array('value' => $arr['param_value']);
    }

    foreach (array('total', 'chaskor', 'chaskor_news', 'wikipedia', 'wikinews', 'blogs', 'fiction') as $src) {
        $stats['goals'][$src.'_words'] = $config['goals'][$src.'_words'];
        $stats['percent_words'][$src] = floor($stats[$src.'_words']['value'] / $config['goals'][$src.'_words'] * 100);
    }

    $stats['added_sentences'] = get_sentence_adders_stats();
    $stats['added_sentences_last_week'] = get_sentence_adders_stats(true);

    // team info
    $uid2team = array();
    $res = sql_query("SELECT user_id, user_team FROM users WHERE user_team > 0");
    while ($r = sql_fetch_array($res))
        $uid2team[$r['user_id']] = $r['user_team'];
    $teams = get_team_list();

    $uid2sid = array();
    $res = sql_query("SELECT user_id, COUNT(*) AS cnt FROM morph_annot_instances WHERE answer > 0 GROUP BY user_id ORDER BY cnt DESC");
    while ($r = sql_fetch_array($res)) {
        $stats['annotators'][] = array('total' => number_format($r['cnt'], 0, '', ' '), 'user_id' => $r['user_id']);
        $uid2sid[$r['user_id']] = sizeof($stats['annotators']) - 1;
        if (isset($uid2team[$r['user_id']]))
            $teams[$uid2team[$r['user_id']]]['total'] += $r['cnt'];
    }

    $stats['teams'] = $teams;

    // last activity info
    $last_click = array();
    $res = sql_query("SELECT user_id, MAX(timestamp) AS last_time FROM morph_annot_click_log GROUP BY user_id");
    while ($r = sql_fetch_array($res)) {
        $last_click[$r['user_id']] = $r['last_time'];
    }

    // divergence info
    $divergence = array();
    $res = sql_query("SELECT user_id, param_value FROM user_stats WHERE param_id = 34");
    while ($r = sql_fetch_array($res)) {
        $divergence[$r['user_id']] = $r['param_value'];
    }

    $res = sql_query("SELECT u.user_id, u.user_shown_name AS user_name, param_value FROM user_stats s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE param_id=33 ORDER BY param_value DESC");
    while ($r = sql_fetch_array($res)) {
        $t = array(
            'user_name' => $r['user_name'],
            'value' => number_format($r['param_value'], 0, '', ' '),
            'divergence' => $divergence[$r['user_id']] / $r['param_value'] * 100,
            'last_active' => $last_click[$r['user_id']]
        );
        $stats['annotators'][$uid2sid[$r['user_id']]]['fin'] = $t;
    }

    foreach ($stats['annotators'] as $k => $v) {
        if (!isset($v['fin']['user_name'])) {
            $stats['annotators'][$k]['fin']['user_name'] = get_user_shown_name($v['user_id']);
            $stats['annotators'][$k]['fin']['last_active'] = $last_click[$v['user_id']];
        }
    }

    // we need 2 timestamps to show last activity
    $stats['timestamp_yesterday'] = ($stats['timestamp_today'] = mktime(0, 0, 0)) - 3600 * 24;

    $stats['_chart'] = get_word_stats_for_chart();

    return $stats;
}
function get_sentence_adders_stats($last_week=false) {
    if ($last_week)
        $param = 7;
    else
        $param = 6;

    $out = array();
    $res = sql_query("SELECT user_shown_name AS user_name, param_value FROM user_stats LEFT JOIN users USING (user_id) WHERE param_id=$param ORDER BY param_value DESC");
    while ($r = sql_fetch_array($res)) {
        $out[] = array('user_name' => $r['user_name'], 'value' => $r['param_value']);
    }
    return $out;
}
function get_word_stats_for_chart() {
    $chart = array();
    $t = array();
    $tchart = array();
    $time = time();

    $param_set = array(32, 27, 23, 19, 15, 11);

    foreach ($param_set as $param_id) {
        $res = sql_query("SELECT timestamp, param_value FROM stats_values WHERE timestamp > ".($time - 90*24*60*60)." AND param_id = $param_id ORDER BY timestamp");
        while ($r = sql_fetch_array($res)) {
            $day = intval($r['timestamp'] / 86400);
            $t[$day][$param_id] = $r['param_value'];
        }
    }
    ksort($t);

    // we need two cycles for cases when a parameter was measured more than once a day
    // we suppose that all parameters were measured simultaneously

    foreach ($t as $day => $ar) {
        $sum = 0;
        foreach ($param_set as $param_id) {
            $sum += $ar[$param_id];
            $tchart[$param_id][] = '['.($day * 86400000).','.$sum.']';
        }
    }

    $chart['chaskor_words'] = join(',', $tchart[11]);
    $chart['wikinews_words'] = join(',', $tchart[15]);
    $chart['wikipedia_words'] = join(',', $tchart[19]);
    $chart['blogs_words'] = join(',', $tchart[23]);
    $chart['chaskor_news_words'] = join(',', $tchart[27]);
    $chart['fiction_words'] = join(',', $tchart[32]);

    return $chart;
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
    global $config;
    $dict = array();
    $annot = array();
    $ngram = array();
    $ngram_path = 'files/export/ngrams/';

    $dict['xml'] = get_file_info('files/export/dict/dict.opcorpora.xml');
    $dict['txt'] = get_file_info('files/export/dict/dict.opcorpora.txt');
    $annot['xml'] = get_file_info('files/export/annot/annot.opcorpora.xml');

    $types1 = array('exact', 'exact_lc', 'exact_cyr', 'exact_cyr_lc');
    $types2 = array('exact', 'exact_lc', 'exact_cyrA', 'exact_cyrB', 'exact_cyrA_lc', 'exact_cyrB_lc');
    $i2word = array(1 => 'unigrams', 2 => 'bigrams', 3 => 'trigrams');

    for ($i = 1; $i <= 3; ++$i) {
        $arr = ($i == 1) ? $types1 : $types2;
        foreach ($arr as $type) {
            $ngram[$i][$type] = get_file_info($ngram_path.$i2word[$i].$config['ngram_suffixes'][$type]);
        }
    }

    $colloc['mi'] = get_file_info($ngram_path.'/colloc.MI');

    return array('dict'=>$dict, 'annot'=>$annot, 'ngram'=>$ngram, 'colloc'=>$colloc);
}
function get_file_info($path) {
    //get size and time info about a group of archives
    $mb = 1024 * 1024;
    $out = array();
    $stat = stat($path.'.bz2');
    $out['bz2']['size'] = sprintf("%.2f", $stat[7] / $mb);
    $out['updated'] = date('d.m.Y H:i \M\S\K', $stat[9]);
    $stat = stat($path.'.zip');
    $out['zip']['size'] = sprintf("%.2f", $stat[7] / $mb);
    return $out;
}
function get_top100_info($what, $type) {
    global $config;
    $stats = array();

    $filename = '';
    list($N, $ltype) = explode('_', $type, 2);
    switch ($N) {
        case 1:
            $filename = 'unigrams';
            break;
        case 2:
            $filename = 'bigrams';
            break;
        case 3:
            $filename = 'trigrams';
            break;
        default:
            return $stats;
    }
    
    if (isset($config['ngram_suffixes'][$ltype]))
        $filename .= $config['ngram_suffixes'][$ltype];
    else
        return $stats;

    $f = file($config['project']['root']."/files/export/ngrams/$filename.top100");

    if ($what == 'colloc') {
        foreach ($f as $s) {
            list($lterm, $rterm, $lfreq, $rfreq, $cfreq, $coeff) = explode("\t", $s);
            $stats[] = array('lterm' => $lterm, 'rterm' => $rterm, 'lfreq' => $lfreq, 'rfreq' => $rfreq, 'cfreq' => $cfreq, 'coeff' => $coeff);
        }
    } else {
        foreach ($f as $s) {
            list($token, $abs, $ipm) = explode("\t", $s);
            $stats[] = array('token' => $token, 'abs' => $abs, 'ipm' => $ipm);
        }
    }
    
    return $stats;
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

function safe_read($file, $length) {
    $fp = fopen($file, 'r');
    if (!is_resource($fp)) {
        return FALSE;
    }
    flock($fp, LOCK_SH);
    $data = fread($fp, $length);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $data;
}

function safe_write($file, $mode, $data) {
    $fp = fopen($file, $mode);
    if (!is_resource($fp)) {
        return FALSE;
    }
    flock($fp, LOCK_EX);
    fwrite($fp, $data);
    flock($fp, LOCK_UN);
    fclose($fp);

    return TRUE;
}

?>
