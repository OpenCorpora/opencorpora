<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

set_include_path(get_include_path().PATH_SEPARATOR.'/corpus');
require_once('lib/header_ajax.php');
require_once('lib/lib_tokenizer.php');
require_once('lib/lib_books.php');

$options = getopt("df:hp:t:u:");
main($options);


function main($opts) {
    print_r($opts);
    if (isset($opts['h']) || !$opts['f'] || !isset($opts['t']) || (!isset($opts['d']) && (!$opts['p'] || !isset($opts['u'])))) {
        print "NOTE: title becomes the first paragraph as well!\nOptions:
        -f\t<path>\tinput file
        -t\t<float>\ttokenizer threshold
        -p\t<int>\tparent text id
        -u\t<int>\tid of the user who will be the text adder, may be 0
        -d\tdry run, doesn't insert anything, prints tokenized text
        -h\tshow this help\n";
        exit();
    }
    $dry_run = isset($opts['d']);
    list($tags, $title, $textlines) = read_input($opts['f']);
    $text = prepare_paragraphs($textlines);
    sql_begin();
    if (!$dry_run) {
        $book_id = books_add($title, $opts['p']);
        print "Created book #" . $book_id . "\n";
        foreach ($tags as $tag) {
            books_add_tag($book_id, $tag);
        }
        $user_id = $opts['u'];
        $revset_id = create_revset('', $user_id);
    }
    $tokenizer = new Tokenizer(__DIR__ . '/tokenizer');

    if (!$dry_run) {
        $par_ins = sql_prepare("INSERT INTO `paragraphs` VALUES(NULL, ?, ?)");
        $par_num = 1;
        $sent_ins = sql_prepare("INSERT INTO `sentences` VALUES(NULL, ?, ?, ?, 0)");
        $token_ins = sql_prepare("INSERT INTO `tokens` VALUES(NULL, ?, ?, ?)");
    }

    foreach ($text as $par) {
        if (!sizeof($par)) continue;
        if (!$dry_run) {
            sql_execute($par_ins, array($book_id, $par_num++));
            $par_id = sql_insert_id();
        }
        $sent_num = 1;
        foreach ($par as $sent) {
            if (!preg_match('/\S/', $sent)) continue;
            if (!$dry_run) {
                sql_execute($sent_ins, array($par_id, $sent_num++, $sent));
                $sent_id = sql_insert_id();
                sql_query("INSERT INTO sentence_authors VALUES($sent_id, $user_id, ".time().")");
            }
            $token_num = 1;
            $tokens = $tokenizer->tokenize($sent, $opts['t']);
            foreach ($tokens as $token) {
                $tt = trim($token->text);
                if ($tt === '') continue;
                if ($dry_run) {
                    print $tt . ' ';
                }
                else {
                    sql_execute($token_ins, array($sent_id, $token_num++, $tt));
                    $tf_id = sql_insert_id();
                    $parse = new MorphParseSet(false, $tt);
                    create_tf_revision($revset_id, $tf_id, $parse->to_xml());
                }
            }
            if ($dry_run) print "\n";
        }
        if ($dry_run) print "\n";
    }
    sql_commit();
}

function read_input($path) {
    $taglines = array();
    $title = '';
    $textlines = array();
    $in_tags = true;
    $in_text = false;

    foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);
        if ($in_tags) {
            if ($line) {
                $taglines[] = $line;
            }
            else
                $in_tags = false;
        }
        elseif ($in_text) {
            $textlines[] = $line;
        }
        elseif (!$title) {
            $title = $line;
            $textlines = array($title);
            $in_text = true;
        }
        else {
            die("Text structure failure");
        }
    }

    return array($taglines, $title, $textlines);
}

function prepare_paragraphs($textlines) {
    $text = array(array());
    foreach ($textlines as $line) {
        if ($line) {
            $text[sizeof($text) - 1][] = remove_bad_symbols($line);
        }
        elseif (sizeof($text[sizeof($text) - 1]) > 0) {
            $text[] = array();
        }
        // else there are multiple empty lines between paragraphs, allow this
    }
    return $text;
}

?>
