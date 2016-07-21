<?php
if (!headers_sent()) {
    session_start();
    header('Content-type: application/json');
}

$config = parse_ini_file(__DIR__ . '/../config.ini', true);
require_once(__DIR__.'/../vendor/autoload.php'); // Smarty, something else which was installed via Composer

require_once('common.php');
require_once('constants.php');
require_once('timer.php');

$transaction_counter = 0;
$nested_transaction_counter = 0;

$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$pdo_db->query("SET NAMES utf8");

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

$result = array('error' => 0);  // this will end up being json-encoded and returned
?>
