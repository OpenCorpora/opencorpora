<?php
if (!headers_sent()) {
    session_start();
    header("Content-type: text/xml; charset=utf-8");
}

$config = parse_ini_file(__DIR__ . '/../config.ini', true);

require_once('common.php');

$transaction_counter = 0;
$nested_transaction_counter = 0;

$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$pdo_db->query("SET NAMES utf8");
?>
