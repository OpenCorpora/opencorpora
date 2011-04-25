<?php
if (!headers_sent()) {
    session_start();
    #update access time in order that session doesn't expire due to not writing to it
    if (!isset($_SESSION['last_access']) || (time() - $_SESSION['last_access']) > 60) {
        $_SESSION['last_access'] = time();
    }
    header("Content-type: text/html; charset=utf-8");
}
require_once('config.php');
require_once('common.php');

//init Smarty
require_once('Smarty.class.php');

$smarty = new Smarty();
$smarty->template_dir = $config['smarty_template_dir'];
$smarty->compile_dir  = $config['smarty_compile_dir'];
$smarty->config_dir   = $config['smarty_config_dir'];
$smarty->cache_dir    = $config['smarty_cache_dir'];
$smarty->registerPlugin("block", "t", "translate");

//language issues
if (isset($_SESSION['options'])) {
    $lang_id = $_SESSION['options'][2];
} else {
    $lang_id = 1;
}

switch($lang_id) {
    case 1:
        $lang = 'ru';
        $locale = 'ru_RU';
        break;
    case 2:
        $lang = 'en';
        $locale = 'en_US';
        break;
}

$smarty->compile_id = $lang_id;
$smarty->assign('lang', $lang);

putenv('LC_ALL='.$locale);
putenv('LANG='.$locale);
putenv('LANGUAGE='.$locale);
if (!setlocale(LC_ALL, $locale.'.utf8', $locale.'.utf-8', $locale.'UTF8', $locale.'UTF-8', $lang.'utf-8', $lang.'UTF-8', $lang)) {
    setlocale(LC_ALL, '');
}

bindtextdomain('messages', 'locale');
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');

//database connect
$db = mysql_connect($config['mysql_host'], $config['mysql_user'], $config['mysql_passwd']) or die ("Unable to connect to mysql server");
if (!sql_query("USE ".$config['mysql_dbname'], 0, 1)) {
    die ("Unable to open mysql database");
}
sql_query("SET names utf8", 0, 1);

//debug mode
if (is_admin() && isset($_GET['debug']) && $debug = $_GET['debug']) {
    if ($debug == 'on' && !isset($_SESSION['debug_mode'])) {
        $_SESSION['debug_mode'] = 1;
    } elseif ($debug == 'off' && $_SESSION['debug_mode']) {
        unset ($_SESSION['debug_mode']);
    }
    header("Location:".$_SERVER['HTTP_REFERER']);
    return;
}

//language change
if (isset($_GET['lang']) && $lang = $_GET['lang']) {
    if ($lang == 'ru') {
        $_SESSION['options'][2] = 1;
    }
    elseif ($lang == 'en') {
        $_SESSION['options'][2] = 2;
    }

    header("Location:".$_SERVER['HTTP_REFERER']);
    return;
}

//admin pretends that he is a user
if (is_logged() && $_SESSION['user_group'] > 5 && isset($_GET['pretend']) && $pretend = $_GET['pretend']) {
    if ($pretend == 'on')
        $_SESSION['user_group'] = 6;
    elseif ($pretend == 'off')
        $_SESSION['user_group'] = 7;
    header("Location:".$_SERVER['HTTP_REFERER']);
    return;
}

//some globals
$smarty->assign('web_prefix', $config['web_prefix']);
$smarty->assign('is_admin', is_admin() ? 1 : 0);
$smarty->assign('is_logged', is_logged() ? 1 : 0);
$smarty->assign('readonly', file_exists('/var/lock/oc_readonly.lock') ? 1 : 0);
$smarty->assign('dict_errors', sql_num_rows(sql_query("SELECT error_id FROM dict_errata LIMIT 1")));

//svn info
$svnfile = file('.svn/entries');
$smarty->assign('svn_revision', $svnfile[3]);
?>
