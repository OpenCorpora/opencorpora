<?php
require_once('lib_db.php');
require_once('lib_users.php');

// For logger, see log() below
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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

class PermissionError extends Exception {}
class NotLoggedError extends Exception {}

function log_data($str, $context=array()) {
    static $logger = NULL;
    if (!$logger) {
        // create a log channel
        $logger = new Logger('general');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../logs/general.log'));
    }
    $logger->debug($str, (array)$context);
}

function oc_exception_handler($exception) {
    log_data("exception:", $exception);
    if ($exception instanceof PermissionError)
        show_error("У вас недостаточно прав для просмотра этой страницы.");
    elseif ($exception instanceof NotLoggedError)
        show_error("Для выполнения этого действия нужно <a href='/login.php'>войти</a> в свою учётную запись.");
    else
        show_error("Произошла ошибка.<br/><br/>" . $exception->getMessage());
}

function ENSURE($array, $param_name, $default_val) {
    if (isset($array[$param_name]))
        return $array[$param_name];
    if (!is_null($default_val))
        return $default_val;
    throw new UnexpectedValueException("Wrong args: missing $param_name");
}

function XGET($glob, $param_name, $value_if_not_set) {
    $ret = ENSURE($glob, $param_name, $value_if_not_set);
    if (is_string($ret))
        $ret = trim($ret);
    return $ret;
}

function POST($param_name, $value_if_not_set = NULL) {
    return XGET($_POST, $param_name, $value_if_not_set);
}

function GET($param_name, $value_if_not_set = NULL) {
    return XGET($_GET, $param_name, $value_if_not_set);
}

function REQUEST($param_name, $value_if_not_set = NULL) {
    return XGET($_REQUEST, $param_name, $value_if_not_set);
}
function create_revset($comment = '', $user_id=0) {
    if (!$user_id) {
        if (!isset($_SESSION['user_id']) || !$_SESSION['user_id'])
            throw new Exception();
        $user_id = $_SESSION['user_id'];
    }

    $now = time();
    global $config;
    // check if there is a recent set by the same user with the same comment
    $timeout = $now - $config['misc']['changeset_timeout'];
    $res = sql_pe("
        SELECT set_id
        FROM rev_sets
        WHERE user_id = ?
        AND timestamp > ?
        AND comment = ?
        ORDER BY set_id DESC
        LIMIT 1
    ", array($user_id, $timeout, $comment));
    if (sizeof($res) > 0) {
        sql_query("UPDATE rev_sets SET timestamp=$now WHERE set_id=".$res[0]['set_id']." LIMIT 1");
        return $res[0]['set_id'];
    }

    $q = "INSERT INTO `rev_sets` VALUES(NULL, ?, ?, ?)";
    sql_pe($q, array($now, $user_id, $comment));
    return sql_insert_id();
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
    $title = urlencode($title);
    $url = "http://localhost/w/api.php?action=query&prop=revisions&titles=$title&rvprop=content&rvparse=1&format=xml";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($ch);
    curl_close($ch);
    return $contents;
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
    $annot['disamb_xml'] = get_file_info('files/export/annot/annot.opcorpora.no_ambig.xml');
    $annot['disamb_xml_strict'] = get_file_info('files/export/annot/annot.opcorpora.no_ambig_strict.xml');
    $annot['disamb_xml_nonmod'] = get_file_info('files/export/annot/annot.opcorpora.no_ambig.nonmod.xml');

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
    if ($what == 'colloc') {
        $filename = 'colloc.MI';
    } else {
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
    }

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
    check_permission(PERM_ADMIN);
    global $config;
    touch_file($config['project']['readonly_flag']);
}
function set_readonly_off() {
    check_permission(PERM_ADMIN);
    global $config;
    unlink($config['project']['readonly_flag']);
}
function touch_file($path) {
    exec("touch ".escapeshellarg($path));
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
/**
* saves alert message to session
* @param string $type alert type [success,error,info]
* @param string $message alert text
*/
function alert_set($type,$message) {
    switch ($type) {
        case 'success':
        case 'error':
        case 'info':
            if (!isset($_SESSION['alert'])) {
                $_SESSION['alert'] = array();
            }
            $_SESSION['alert'][$type] = $message;
            break;
    }
}
/**
* returns alert message & destroys it
* @param string $type alert type [success,error,info]
*/
function alert_get($type = '') {
    if (!isset($_SESSION['alert'])) {
        return false;
    }
    switch ($type) {
        case 'success':
        case 'error':
        case 'info':
            if (isset($_SESSION['alert'][$type])) {
                $message = $_SESSION['alert'][$type];
                unset($_SESSION['alert'][$type]);
                return $message;
            }
            break;
    }
}
/**
* returns all alert messages & destroys them
*/
function alert_getall() {
    if (!isset($_SESSION['alert'])) {
        return array();
    }
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
    return $alert;
}
