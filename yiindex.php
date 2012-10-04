<?php
// include Yii bootstrap file
defined('YII_DEBUG') or define('YII_DEBUG', true);
require_once(dirname(__FILE__).'/../framework/yii.php');
$config = dirname(__FILE__).'/yii/config/main.php';


// create a Web application instance and run
Yii::createWebApplication($config)->run();
?>
