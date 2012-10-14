<?php

/**
 * Staging configuration
 * Usage:
 * - Online website
 * - Production DB
 * - All details on error
 */

return array(
	
    // Set yiiPath (relative to Environment.php)
    //'yiiPath' => dirname(__FILE__) . '/../../../yii/framework/yii.php',
    //'yiicPath' => dirname(__FILE__) . '/../../../yii/framework/yiic.php',
    //'yiitPath' => dirname(__FILE__) . '/../../../yii/framework/yiit.php',

    // Set YII_DEBUG and YII_TRACE_LEVEL flags
    'yiiDebug' => true,
    'yiiTraceLevel' => 0,

    // Static function Yii::setPathOfAlias()
    'yiiSetPathOfAlias' => array(
        // uncomment the following to define a path alias
        //'local' => 'path/to/local-folder'
    ),

    // This is the specific Web application configuration for this mode.
    // Supplied config elements will be merged into the main config array.
    'configWeb' => array(

        // Application components
        'components' => array(

            // Database
            'db' => array(
                'connectionString' => 'mysql:host=STAGING_HOST;dbname=STAGING_DBNAME',
                'username' => 'USERNAME',
                'password' => 'PASSWORD',
            ),

            // Application Log
            'log' => array(
                'class' => 'CLogRouter',
                'routes' => array(
                    // Save log messages on file
                    array(
                        'class' => 'CFileLogRoute',
                        'levels' => 'error, warning, trace, info',
                    ),
                ),
            ),

        ),

    ),

    // This is the Console application configuration. Any writable
    // CConsoleApplication properties can be configured here.
    // Leave array empty if not used.
    // Use value 'inherit' to copy from generated configWeb.
    'configConsole' => array(
    ),

);