<?php
if (!headers_sent()) {
	session_start();
}
require_once('config.php');
require_once('common.php');

#database connect
$db = mysql_connect($config['mysql_host'], $config['mysql_user'], $config['mysql_passwd']) or die ("Unable to open mysql database");
sql_query("USE corpora", 0);
sql_query("SET names utf8", 0);

#debug mode
if (isset($_GET['debug']) && $debug = $_GET['debug']) {
    if ($debug == 'on' && !isset($_SESSION['debug_mode'])) {
        $_SESSION['debug_mode'] = 1;
    } elseif ($debug == 'off' && $_SESSION['debug_mode']) {
        unset ($_SESSION['debug_mode']);
    }
    header("Location:".$_SERVER['HTTP_REFERER']);
}
?>
