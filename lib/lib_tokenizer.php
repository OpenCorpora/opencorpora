<?php
require_once('lib_annot.php');
require_once('lib_books.php');

function split2paragraphs($txt) {
    $pars = array();
    foreach (preg_split('/\r?\n\r?\n\r?/', $txt) as $par)
        if (preg_match('/\S/', $par))
            $pars[] = $par;
    return $pars;
}
function split2sentences($txt) {
    return preg_split('/[\r\n]+/', $txt);
}
function tokenize_ml($txt, $exceptions, $prefixes) {
    $coeff = array();
    $out = array();
    $token = '';

    $txt = Normalizer::normalize($txt, Normalizer::FORM_C);

    $res = sql_query("SELECT * FROM tokenizer_coeff");
    while ($r = sql_fetch_array($res)) {
        $coeff[$r[0]] = $r[1];
    }

    $txt .= '  ';

    for ($i = 0; $i < mb_strlen($txt, 'UTF-8'); ++$i) {
        $prevchar  = ($i > 0 ? mb_substr($txt, $i-1, 1, 'UTF-8') : '');
        $char      =           mb_substr($txt, $i+0, 1, 'UTF-8');
        $nextchar  =           mb_substr($txt, $i+1, 1, 'UTF-8');
        $nnextchar =           mb_substr($txt, $i+2, 1, 'UTF-8');

        //$chain is the current word which we will perhaps need to check in the dictionary

        $chain = $chain_left = $chain_right = '';
        $odd_symbol = '';
        if (is_hyphen($char) || is_hyphen($nextchar)) {
            $odd_symbol = '-';
        }
        elseif (preg_match('/([\.\/\?\=\:&"!\+\(\)])/u', $char, $match) || preg_match('/([\.\/\?\=\:&"!\+\(\)])/u', $nextchar, $match)) {
            $odd_symbol = $match[1];
        }
        if ($odd_symbol) {
            for ($j = $i; $j >= 0; --$j) {
                $t = mb_substr($txt, $j, 1, 'UTF-8');
                if (($odd_symbol == '-' && (is_cyr($t) || is_hyphen($t) || $t === "'")) ||
                    ($odd_symbol != '-' && !is_space($t))) {
                    $chain_left = $t.$chain_left;
                } else {
                    break;
                }
                if (mb_substr($chain_left, -1) === $odd_symbol) {
                    $chain_left = mb_substr($chain_left, 0, -1);
                }
            }
            for ($j = $i+1; $j < mb_strlen($txt, 'UTF-8'); ++$j) {
                $t = mb_substr($txt, $j, 1, 'UTF-8');
                if (($odd_symbol == '-' && (is_cyr($t) || is_hyphen($t) || $t === "'")) ||
                    ($odd_symbol != '-' && !is_space($t))) {
                    $chain_right .= $t;
                } else {
                    break;
                }
                if (mb_substr($chain_right, 0, 1) === $odd_symbol) {
                    $chain_right = mb_substr($chain_right, 1);
                }
            }
            $chain = $chain_left.$odd_symbol.$chain_right;
        }

        $vector = array_merge(char_class($char), char_class($nextchar),
            array(
                is_number($prevchar),
                is_number($nnextchar),
                ($odd_symbol == '-' ? is_dict_chain($chain): 0),
                ($odd_symbol == '-' ? is_suffix($chain_right) : 0),
                is_same_pm($char, $nextchar),
                (($odd_symbol && $odd_symbol != '-') ? looks_like_url($chain, $chain_right) : 0),
                (($odd_symbol && $odd_symbol != '-') ? is_exception($chain, $exceptions) : 0),
                ($odd_symbol == '-' ? is_prefix($chain_left, $prefixes) : 0),
                (($odd_symbol == ':' && $chain_right !== '') ? looks_like_time($chain_left, $chain_right) : 0)
        ));
        $vector = implode('', $vector);

        if (isset($coeff[bindec($vector)])) {
            $sum = $coeff[bindec($vector)];
        } else {
            $sum = 0.5;
        }

        $token .= $char;

        if ($sum > 0) {
            $token = trim($token);
            if ($token !== '') $out[] = array($token, $sum, bindec($vector).'='.$vector);
            $token = '';
        }
    }
    return $out;

}
function uniord($u) {
    $c = unpack("N", mb_convert_encoding($u, 'UCS-4BE', 'UTF-8'));
    return $c[1];
}
function char_class($char) {
    $ret = 
        is_cyr($char)           ? '0001' :
        (is_space($char)        ? '0010' :
        (is_dot($char)          ? '0011' :
        (is_pmark($char)        ? '0100' :
        (is_hyphen($char)       ? '0101' :
        (is_number($char)       ? '0110' :
        (is_latin($char)        ? '0111' :
        (is_bracket1($char)     ? '1000' :
        (is_bracket2($char)     ? '1001' :
        (is_single_quote($char) ? '1010' :
        (is_slash($char)        ? '1011' :
        (is_colon($char)        ? '1100' : '0000')))))))))));
    return str_split($ret);
}
function is_space($char) {
    return preg_match('/^\s$/u', $char);
}
function is_hyphen($char) {
    return (int)($char == '-');
}
function is_slash($char) {
    return (int)($char == '/');
}
function is_dot($char) {
    return (int)($char == '.');
}
function is_colon($char) {
    return (int)($char == ':');
}
function is_single_quote($char) {
    return (int)($char == "'");
}
function is_same_pm($char1, $char2) {
    return (int)($char1===$char2);
}
function is_cyr($char) {
    $re_cyr = '/\p{Cyrillic}/u';
    return preg_match($re_cyr, $char);
}
function is_latin($char) {
    $re_lat = '/\p{Latin}/u';
    return preg_match($re_lat, $char);
}
function is_number($char) {
    return (int)is_numeric($char);
}
function is_pmark($char) {
    $re_punctuation = '/[,!\?;"\xAB\xBB]/u';
    return preg_match($re_punctuation, $char);
}
function is_bracket1($char) {
    $re_bracket = '/[\(\[\{\<]/u';
    return preg_match($re_bracket, $char);
}
function is_bracket2($char) {
    $re_bracket = '/[\)\]\}\>]/u';
    return preg_match($re_bracket, $char);
}
function is_dict_chain($chain) {
    if (!$chain) return 0;
    return (int)(form_exists(mb_strtolower($chain, 'UTF-8')) > 0);
}
function is_suffix($s) {
    return (int)in_array($s, array('то', 'таки', 'с', 'ка', 'де'));
}
function looks_like_url($s, $suffix) {
    if (!$suffix || substr($s, 0, 1) === '.' || mb_strlen($s) < 5)
        return 0;
    $re1 = '/^\W*https?\:\/\/?/u';
    $re2 = '/^\W*www\./u';
    $re3 = '/.\.(?:[a-z]{2,3}|ру|рф)\W*$/iu';
    if (preg_match($re1, $s) || preg_match($re2, $s) || preg_match($re3, $s)) {
        return 1;
    }
    return 0;
}
function looks_like_time($left, $right) {
    $left = preg_replace('/^[^0-9]+/u', '', $left);
    $right = preg_replace('/[^0-9]+$/u', '', $right);

    if (!preg_match('/^[0-9][0-9]?$/u', $left) || !preg_match('/^[0-9][0-9]$/u', $right))
        return 0;

    if ($left < 24 && $right < 60)
        return 1;

    return 0;
}
function is_exception($s, $exc) {
    $s = mb_strtolower($s);
    if (in_array($s, $exc))
        return 1;
    if (!preg_match('/^\W|\W$/u', $s))
        return 0;
    $s = preg_replace('/^[^A-Za-zА-ЯЁа-яё0-9]+/u', '', $s);
    if (in_array($s, $exc))
        return 1;
    while (preg_match('/[^A-Za-zА-Яа-яЁё0-9]$/u', $s)) {
        $s = preg_replace('/[^A-Za-zА-ЯЁа-яё0-9]$/u', '', $s);
        if (in_array($s, $exc))
            return 1;
    }
    return 0;
}
function is_prefix($s, $prefixes) {
    if (in_array(mb_strtolower($s, 'UTF-8'), $prefixes))
        return 1;
    return 0;
}
function addtext_check($array) {
    global $config;

    check_permission(PERM_ADDER);
    //read file for tokenizer
    $tok_exc = array_map('mb_strtolower', file($config['project']['root'] . '/scripts/tokenizer/tokenizer_exceptions.txt', FILE_IGNORE_NEW_LINES));
    $tok_prefixes = file($config['project']['root'] . '/scripts/tokenizer/tokenizer_prefixes.txt', FILE_IGNORE_NEW_LINES);

    //removing bad symbols
    $clear_text = '';
    for ($i = 0; $i < mb_strlen($array['txt'], 'UTF-8'); ++$i) {
        $char = mb_substr($array['txt'], $i, 1, 'UTF-8');
        $code = uniord($char);
        if (
            //remove diacritic modifier
            $code != 769 &&
            //remove soft hyphen
            $code != 173 &&
            //remove different spaces 8206 8207
            ($code < 8192 || $code > 8203) &&
            //char order marks
            !in_array($code, array(8206, 8207)) &&
            //other bad symbols
            !in_array($code, array(160, 8237, 8239, 8288, 12288))
            //the numbers are decimal unicode codes
        ) $clear_text .= $char;
    }

    $out = array('full' => $clear_text, 'select0' => get_books_for_select(0));
    $pars = split2paragraphs($clear_text);
    foreach ($pars as $par) {
        $par_array = array();
        $sents = split2sentences($par);
        foreach ($sents as $sent) {
            if (!preg_match('/\S/', $sent)) continue;
            $sent_array = array('src' => $sent);
            $tokens = tokenize_ml($sent, $tok_exc, $tok_prefixes);
            foreach ($tokens as $token) {
                $sent_array['tokens'][] = array('text' => $token[0], 'class' => form_exists($token[0]), 'border' => $token[1], 'vector' => $token[2]);
            }
            $par_array['sentences'][] = $sent_array;
        }
        $out['paragraphs'][] = $par_array;
    }
    //book
    if (isset($array['book_id'])) {
        $book_id = (int)$array['book_id'];
        $r = sql_fetch_array(sql_query("SELECT parent_id FROM books WHERE book_id=$book_id LIMIT 1"));
        if ($r['parent_id'] > 0) {
            $out['selected0'] = $r['parent_id'];
            $out['select1'] = get_books_for_select($r['parent_id']);
            $out['selected1'] = $book_id;
        } else {
            $out['selected0'] = $book_id;
        }
    }
    return $out;
}
function addtext_add($text, $sentences, $book_id, $par_num) {
    check_permission(PERM_ADDER);
    if (!$text || !$book_id || !$par_num)
        throw new UnexpectedValueException();
    sql_begin();
    $revset_id = create_revset();
    $sent_count = 0;
    $pars = split2paragraphs($text);

    // move the following paragraphs
    sql_query("UPDATE paragraphs SET pos=pos+".sizeof($pars)." WHERE book_id = $book_id AND pos >= $par_num");

    $par_ins = sql_prepare("INSERT INTO `paragraphs` VALUES(NULL, ?, ?)");
    $sent_ins = sql_prepare("INSERT INTO `sentences` VALUES(NULL, ?, ?, ?, 0)");
    $token_ins = sql_prepare("INSERT INTO `tokens` VALUES(NULL, ?, ?, ?)");

    foreach ($pars as $par) {
        //adding a paragraph
        sql_execute($par_ins, array($book_id, $par_num++));
        $par_id = sql_insert_id();
        $sent_num = 1;
        $sents = split2sentences($par);
        foreach ($sents as $sent) {
            if (!preg_match('/\S/', $sent)) continue;
            //adding a sentence
            sql_execute($sent_ins, array($par_id, $sent_num++, trim($sent)));
            $sent_id = sql_insert_id();
            sql_query("INSERT INTO sentence_authors VALUES($sent_id, ".$_SESSION['user_id'].", ".time().")");
            $token_num = 1;
            $tokens = explode('^^', $sentences[$sent_count++]);
            foreach ($tokens as $token) {
                if (trim($token) === '') continue;
                //adding a textform
                sql_execute($token_ins, array($sent_id, $token_num++, trim($token)));
                $tf_id = sql_insert_id();
                //adding a revision
                $parse = new MorphParseSet(false, trim($token));
                create_tf_revision($revset_id, $tf_id, $parse->to_xml());
            }
        }
    }
    sql_commit();
}
function get_monitor_data($from, $until) {
    $query = "
        SELECT
            run,
            threshold,
            `precision`,
            recall,
            F1
        FROM
            tokenizer_qa
        WHERE
            run >= ?
            AND run <= ?
    ";

    $qa_data = array(
        'precision' => array(),
        'recall' => array(),
        'F1' => array()
    );
    $q = sql_pe($query, array($from, $until));
    foreach ($q as $res) {
        $run_date = strtotime($res['run']) * 1000;
        $thrshld  = $res['threshold'];

        $qa_data['precision'][$thrshld][] = array($run_date, (float)$res['precision']);
        $qa_data['recall'][$thrshld][] = array($run_date, (float)$res['recall']);
        $qa_data['F1'][$thrshld][] = array($run_date, (float)$res['F1']);
    }

    return $qa_data;
}
?>
