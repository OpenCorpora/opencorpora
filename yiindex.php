<?php

// set environment
require_once(dirname(__FILE__) . '/protected/extensions/yii-environment/Environment.php');
$env = new Environment();
 
// set debug and trace level
defined('YII_DEBUG') or define('YII_DEBUG', $env->yiiDebug);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', $env->yiiTraceLevel);

// run Yii app
//$env->showDebug(); // show produced environment configuration
require_once($env->yiiPath);
$env->runYiiStatics(); // like Yii::setPathOfAlias()
Yii::createWebApplication($env->configWeb)->run();