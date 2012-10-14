<?php

/**
 * Production configuration
 * Usage:
 * - Online website
 * - Production DB
 * - Standard production error pages (404, 500, etc.)
 */

$ini_file = parse_ini_file(dirname(__FILE__) . '/../../config.ini', true);
return array(
	
    // Set yiiPath (relative to Environment.php)
    'yiiPath' => '/var/yii/yii.php',
    'yiicPath' => '/var/yii/yiic.php',
    'yiitPath' => '/var/yii/yiit.php',
    // Set YII_DEBUG and YII_TRACE_LEVEL flags
    'yiiDebug' => false,
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
                'connectionString' => 'mysql:host=localhost;dbname=opcorpora',
                'username' => $ini_file['mysql']['user'],
                'password' => $ini_file['mysql']['passwd'],
                //'schemaCachingDuration' => 3600,
            ),

            // Application Log
            'log' => array(
                'class' => 'CLogRouter',
                'routes' => array(
                    // Save log messages on file
                    array(
                        'class' => 'CFileLogRoute',
                        'levels' => 'error, warning',
                    ),
                    // Send errors via email to the system admin
                    array(
                        'class' => 'CEmailLogRoute',
                        'levels' => 'error, warning',
                        'emails' => 'grand@opencorpora.org, mary.nikolaeva@gmail.com',
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

        // Application components
        'components' => array(

            // Application Log
            'log' => 'inherit',

        ),

    ),

);
