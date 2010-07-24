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
if (!sql_query("USE ".$config['mysql_dbname'], 0)) {
    die ("Unable to open mysql database");
}
sql_query("SET names utf8", 0);

//debug mode
if (isset($_GET['debug']) && $debug = $_GET['debug']) {
    if ($debug == 'on' && !isset($_SESSION['debug_mode'])) {
        $_SESSION['debug_mode'] = 1;
    } elseif ($debug == 'off' && $_SESSION['debug_mode']) {
        unset ($_SESSION['debug_mode']);
    }
    header("Location:".$_SERVER['HTTP_REFERER']);
}

//some globals
if (stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
    $smarty->assign('bad_browser', 1);
$smarty->assign('web_prefix', $config['web_prefix']);
$smarty->assign('is_admin', is_admin() ? 1 : 0);
$smarty->assign('is_logged', is_logged() ? 1 : 0);
?>
