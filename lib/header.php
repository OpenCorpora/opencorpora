<?php
session_start();
require_once('config.php');
require_once('common.php');

#database connect
if ($config['db_type']=='sqlite') {
    $db = sqlite_open($config['sqlite_dbname']) or die ("Unable to open sqlite database");
} else if ($config['db_type']=='mysql') {
    $db = mysql_connect($config['mysql_host'], $config['mysql_user'], $config['mysql_passwd']) or die ("Unable to open mysql database");
    sql_query("USE corpora");
    sql_query("SET CHARACTER SET utf8");
}

#get user info
?>
