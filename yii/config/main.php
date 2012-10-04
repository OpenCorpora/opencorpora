<?php
return array(
    'name' => 'OpenCorpora',
    'basePath' => dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'defaultController' => 'site',
    'layout' => 'common',
    'components' => array(
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
                array(
                    'class' => 'CWebLogRoute'
                )
            )
        )
    )
);
?>
