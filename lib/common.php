<?php
require_once('lib_users.php');

//sql wrappers
function sql_query($q, $debug=1, $override_readonly=0) {
    global $total_time;
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
        printf ("<span class='debug'>SQL: %s # %.4f сек. (current total time: %.4f сек.)</span><br/>\n", htmlspecialchars($q), $time, $total_time);
        if ($err = mysql_error()) {
            print "<span class='debug_error'>$err</span><br/>\n";
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

    foreach(array('total', 'chaskor', 'chaskor_news', 'wikipedia', 'wikinews', 'blogs', 'fiction') as $src) {
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

    //for the charts
    $chart = array();

    $t = array();
    $tchart = array();

    $param_set = array(32, 27, 23, 19, 15, 11);

    foreach($param_set as $param_id) {
        $res = sql_query("SELECT timestamp, param_value FROM stats_values WHERE param_id = $param_id ORDER BY timestamp");
        while ($r = sql_fetch_array($res)) {
            $day = intval($r['timestamp'] / 86400);
            $t[$day][$param_id] = $r['param_value'];
        }
    }
    ksort($t);

    // we need two cycles for cases when a parameter was measured more than once a day
    // we suppose that all parameters were measured simultaneously

    foreach($t as $day => $ar) {
        $sum = 0;
        foreach($param_set as $param_id) {
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

    //user stats
    $res = sql_query("SELECT timestamp, u.user_name, param_value FROM user_stats s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE param_id=6 ORDER BY param_value DESC");
    $t = array();
    while ($r = sql_fetch_array($res)) {
        $t[] = '{label: "'.$r['user_name'].'", data: '.$r['param_value'].'}';
    }
    $chart['user_stats_full'] = join(', ', $t);
    $res = sql_query("SELECT timestamp, u.user_name, param_value FROM user_stats s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE param_id=7 ORDER BY param_value DESC");
    $t = array();
    while ($r = sql_fetch_array($res)) {
        $t[] = '{label: "'.$r['user_name'].'", data: '.$r['param_value'].'}';
    }
    $chart['user_stats_week'] = join(', ', $t);

    $stats['_chart'] = $chart;

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
    $annot = array();
    $ngram = array();

    $dict['xml'] = get_file_info('files/export/dict/dict.opcorpora.xml');
    $dict['txt'] = get_file_info('files/export/dict/dict.opcorpora.txt');
    $annot['xml'] = get_file_info('files/export/annot/annot.opcorpora.xml');
    $ngram[1]['exact'] = get_file_info('files/export/ngrams/unigrams');
    $ngram[1]['exact_lc'] = get_file_info('files/export/ngrams/unigrams.lc');
    $ngram[1]['exact_cyr'] = get_file_info('files/export/ngrams/unigrams.cyr');
    $ngram[1]['exact_cyr_lc'] = get_file_info('files/export/ngrams/unigrams.cyr.lc');
    $ngram[2]['exact'] = get_file_info('files/export/ngrams/bigrams');
    $ngram[2]['exact_lc'] = get_file_info('files/export/ngrams/bigrams.lc');
    $ngram[2]['exact_cyrA'] = get_file_info('files/export/ngrams/bigrams.cyrA');
    $ngram[2]['exact_cyrB'] = get_file_info('files/export/ngrams/bigrams.cyrB');
    $ngram[2]['exact_cyrA_lc'] = get_file_info('files/export/ngrams/bigrams.cyrA.lc');
    $ngram[2]['exact_cyrB_lc'] = get_file_info('files/export/ngrams/bigrams.cyrB.lc');
    $ngram[3]['exact'] = get_file_info('files/export/ngrams/trigrams');
    $ngram[3]['exact_lc'] = get_file_info('files/export/ngrams/trigrams.lc');
    $ngram[3]['exact_cyrA'] = get_file_info('files/export/ngrams/trigrams.cyrA');
    $ngram[3]['exact_cyrB'] = get_file_info('files/export/ngrams/trigrams.cyrB');
    $ngram[3]['exact_cyrA_lc'] = get_file_info('files/export/ngrams/trigrams.cyrA.lc');
    $ngram[3]['exact_cyrB_lc'] = get_file_info('files/export/ngrams/trigrams.cyrB.lc');

    return array('dict'=>$dict, 'annot'=>$annot, 'ngram'=>$ngram);
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
function get_ngram_top100_info($type) {
    global $config;
    $stats = array();

    $filename = '';
    switch($type) {
        case '1_exact':
            $filename = 'unigrams';
            break;
        case '1_exact_lc':
            $filename = 'unigrams.lc';
            break;
        case '1_exact_cyr':
            $filename = 'unigrams.cyr';
            break;
        case '1_exact_cyr_lc':
            $filename = 'unigrams.cyr.lc';
            break;
        case '2_exact':
            $filename = 'bigrams';
            break;
        case '2_exact_lc':
            $filename = 'bigrams.lc';
            break;
        case '2_exact_cyrA':
            $filename = 'bigrams.cyrA';
            break;
        case '2_exact_cyrB':
            $filename = 'bigrams.cyrB';
            break;
        case '2_exact_cyrA_lc':
            $filename = 'bigrams.cyrA.lc';
            break;
        case '2_exact_cyrB_lc':
            $filename = 'bigrams.cyrB.lc';
            break;
        case '3_exact':
            $filename = 'trigrams';
            break;
        case '3_exact_lc':
            $filename = 'trigrams.lc';
            break;
        case '3_exact_cyrA':
            $filename = 'trigrams.cyrA';
            break;
        case '3_exact_cyrB':
            $filename = 'trigrams.cyrB';
            break;
        case '3_exact_cyrA_lc':
            $filename = 'trigrams.cyrA.lc';
            break;
        case '3_exact_cyrB_lc':
            $filename = 'trigrams.cyrB.lc';
            break;
        default:
            return $stats;
    }

    $f = file($config['project']['root']."/files/export/ngrams/$filename.top100");
    foreach($f as $s) {
        list($token, $abs, $ipm) = explode("\t", $s);
        $stats[] = array('token' => $token, 'abs' => $abs, 'ipm' => $ipm);
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
    if(!is_resource($fp)) {
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
    if(!is_resource($fp)) {
        return FALSE;
    }
    flock($fp, LOCK_EX);
    fwrite($fp, $data);
    flock($fp, LOCK_UN);
    fclose($fp);

    return TRUE;
}

?>
