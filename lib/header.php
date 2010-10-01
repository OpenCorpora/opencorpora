<?php
if (!headers_sent()) {
    session_start();
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

//svn info
$svnfile = file('.svn/entries');
$smarty->assign('svn_revision', $svnfile[3]);
?>
