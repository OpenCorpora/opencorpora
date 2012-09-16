<?php
if (!headers_sent()) {
    session_start();
    header("Content-type: text/xml; charset=utf-8");
}

$config = parse_ini_file(dirname(__FILE__) . '/../config.ini', true);

require_once('common.php');

$db = mysql_connect($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['passwd']) or die ("Unable to connect to mysql server");
if (!sql_query("USE ".$config['mysql']['dbname'], 0, 1)) {
    die ("Unable to open mysql database");
}
sql_query("SET names utf8", 0, 1);
$transaction_counter = 0;
$nested_transaction_counter = 0;
?>
