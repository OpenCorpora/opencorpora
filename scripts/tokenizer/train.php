<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

set_include_path(get_include_path().PATH_SEPARATOR.'/corpus');
require_once('lib/header_ajax.php');
require_once('lib/lib_tokenizer.php');

$tokenizer = new Tokenizer(__DIR__);
$tokenizer->train();
