<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

$limit = array();
if ($argc > 1) {
    if(in_array($argv[1], array('--help', '-help', '-h', '-?')))
        die("Скрипт для поиска кандидатов в мультитокены. Поиск шаблонов из rules.txt \nЗапуск без аргументов или с единственным аргументом - ограничением строк(и) в rules.txt в формате N или N-M.\nСтроки нумеруются с 1.\n");
    else {
        $str = $argv[1];
        $borders = explode("-", $str);
        if (sizeof($borders) > 1)
            $limit = range((int)$borders[0]-1, (int)$borders[1]-1);
        else
            $limit = array((int)$str-1);
    }
}

set_include_path(get_include_path().PATH_SEPARATOR.'/corpus');
require_once('lib/header_ajax.php');
require_once('lib/lib_multiwords.php');

$searcher = new MultiWordFinder(getcwd() . "/rules.txt", $limit);
$searcher->find();
