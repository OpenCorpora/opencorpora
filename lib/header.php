<?php
if (!headers_sent()) {
    session_start();
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
$transaction_counter = 0;
$nested_transaction_counter = 0;

//cookie check
if (!is_logged() && isset($_COOKIE['auth'])) {
    if ($user_id = check_auth_cookie()) {
        if (user_login('', '', $user_id, $_COOKIE['auth'])) {
            header("Location:".$_SERVER['SCRIPT_NAME']);
            return;
        }
    }
}

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
if (is_logged() && isset($_SESSION['user_permissions']['perm_admin']) && $_SESSION['user_permissions']['perm_admin'] == 1 && isset($_GET['pretend']) && $pretend = $_GET['pretend']) {
    if ($pretend == 'on')
        $_SESSION['user_permissions']['pretend'] = 1;
    elseif ($pretend == 'off')
        unset($_SESSION['user_permissions']['pretend']);
    header("Location:".$_SERVER['HTTP_REFERER']);
    return;
}

//some globals
$smarty->assign('web_prefix', $config['web_prefix']);
$smarty->assign('is_admin', is_admin() ? 1 : 0);
$smarty->assign('is_logged', is_logged() ? 1 : 0);
if (is_logged()) {
    $smarty->assign('is_openid', is_user_openid($_SESSION['user_id']) ? 1 : 0);
}
$smarty->assign('user_permission_dict', user_has_permission('perm_dict') ? 1 : 0);
$smarty->assign('user_permission_disamb', user_has_permission('perm_disamb') ? 1 : 0);
$smarty->assign('user_permission_adder', user_has_permission('perm_adder') ? 1 : 0);
$smarty->assign('user_permission_check_tokens', user_has_permission('perm_check_tokens') ? 1 : 0);
$smarty->assign('user_permission_check_morph', user_has_permission('perm_check_morph') ? 1 : 0);
$smarty->assign('readonly', file_exists('/var/lock/oc_readonly.lock') ? 1 : 0);
$smarty->assign('dict_errors', sql_num_rows(sql_query("SELECT error_id FROM dict_errata LIMIT 1")));
$smarty->assign('goals', $config['goals']);

//svn info
$svnfile = file('.svn/entries');
$smarty->assign('svn_revision', $svnfile[3]);
?>
