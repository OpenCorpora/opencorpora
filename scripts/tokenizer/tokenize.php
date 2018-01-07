<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

set_include_path(get_include_path().PATH_SEPARATOR.'/corpus');
require_once('lib/header_ajax.php');
require_once('lib/lib_tokenizer.php');

$tokenizer = new Tokenizer(__DIR__);
while (false !== ($line = fgets(STDIN))) {
    foreach ($tokenizer->tokenize($line) as $token) {
        echo implode("\t", array($token->start_pos, $token->end_pos, $token->get_feats_str_binary(), $token->border_weight)) . "\n";
    }
}

?>
