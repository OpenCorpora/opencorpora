<?php

$config = parse_ini_file(__DIR__ . '/config.ini', true);

return array(
   'paths' => array(
       'migrations' => __DIR__.'/migrations',
       ),
   'environments' => array(
       'default_migration_table' => 'phinxlog',
       'default_database' =>  'corpora',
       'production' => array(
           'adapter' => 'mysql',
           'host' => $config['mysql']['host'],
           'name' => $config['mysql']['dbname'],
           'user' => $config['mysql']['user'],
           'pass' => $config['mysql']['passwd'],
           'port' => '3306',
           'charset' => 'utf8',
        ),
       'development' => array(
           'adapter' => 'mysql',
           'host' => '127.0.0.1',
           'name' => 'corpora',
           'user' => 'root',
           'pass' => '',
           'port' => '3306',
           'charset' => 'utf8'
        ),
    ),
);
