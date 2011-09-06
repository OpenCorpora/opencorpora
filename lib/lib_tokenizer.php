<?php
require_once('lib_books.php');

function split2paragraphs($txt) {
    return preg_split('/\r?\n\r?\n\r?/', $txt);
}
function split2sentences($txt) {
    return preg_split('/[\r\n]+/', $txt);
}
function tokenize_ml($txt, $exceptions) {
    $coeff = array();
    $out = array();
    $token = '';

    $res = sql_query("SELECT * FROM tokenizer_coeff");
    while($r = sql_fetch_array($res)) {
        $coeff[$r[0]] = $r[1];
    }

    //let's first remove diacritics
    $clear_txt = '';
    for ($i = 0; $i < mb_strlen($txt, 'UTF-8'); ++$i) {
        $char = mb_substr($txt, $i, 1, 'UTF-8');
        if (uniord($char) == 769) continue;
        $clear_txt .= $char;
    }
    print "toknizing ".htmlspecialchars($clear_txt)."<br/>";
    $txt = $clear_txt.'  ';

    for($i = 0; $i < mb_strlen($txt, 'UTF-8'); ++$i) {
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
        elseif (preg_match('/([\.\/\?\=\:&"!])/u', $char, $match) || preg_match('/([\.\/\?\=\:&"!])/u', $nextchar, $match)) {
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
                if (mb_substr($chain_left, -1) == $odd_symbol) {
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
                if (mb_substr($chain_right, 0, 1) == $odd_symbol) {
                    $chain_right = mb_substr($chain_right, 1);
                }
            }
            $chain = $chain_left.$odd_symbol.$chain_right;
        }

        $vector = array(
            is_space($char),
            is_space($nextchar),
            is_pmark($char),
            is_pmark($nextchar),
            is_latin($char),
            is_latin($nextchar),
            is_cyr($char),
            is_cyr($nextchar),
            is_hyphen($char),
            is_hyphen($nextchar),
            is_number($prevchar),
            is_number($char),
            is_number($nextchar),
            is_number($nnextchar),
            ($odd_symbol == '-' ? is_dict_chain($chain): 0),
            is_dot($char),
            is_dot($nextchar),
            is_bracket1($char),
            is_bracket1($nextchar),
            is_bracket2($char),
            is_bracket2($nextchar),
            is_single_quote($char),
            is_single_quote($nextchar),
            ($odd_symbol == '-' ? is_suffix($chain_right) : 0),
            is_same_pm($char, $nextchar),
            is_slash($char),
            is_slash($nextchar),
            (($odd_symbol && $odd_symbol != '-') ? looks_like_url($chain, $chain_right) : 0),
            (($odd_symbol && $odd_symbol != '-') ? is_exception($chain, $exceptions) : 0)
        );
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
function is_single_quote($char) {
    return (int)($char == "'");
}
function is_same_pm($char1, $char2) {
    return (int)($char1===$char2);
}
function is_cyr($char) {
    $re_cyr = '/[А-Яа-яЁё]/u';
    return preg_match($re_cyr, $char);
}
function is_latin($char) {
    $re_lat = '/[A-Za-z]/u';
    return preg_match($re_lat, $char);
}
function is_number($char) {
    return (int)is_numeric($char);
}
function is_pmark($char) {
    $re_punctuation = '/[,!\?;:"\xAB\xBB]/u';
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
    if (!$suffix)
        return 0;
    if (substr($s, 0, 1) === '.')
        return 0;
    $re1 = '/^\W*https?\:\/\/u';
    $re2 = '/.\.(ru|ua|com|org|gov|us|ру)\W*$/iu';
    if (preg_match($re1, $s) || preg_match($re2, $s)) {
        return 1;
    }
    return 0;
}
function is_exception($s, $exc) {
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
function addtext_check($array) {
    //read file for tokenizer
    $tok_exc = file('/corpus/scripts/lists/tokenizer_exceptions.txt', FILE_IGNORE_NEW_LINES);

    $out = array('full' => $array['txt'], 'select0' => get_books_for_select(0));
    $pars = split2paragraphs($array['txt']);
    foreach ($pars as $par) {
        if (!preg_match('/\S/', $par)) continue;
        $par_array = array();
        $sents = split2sentences($par);
        foreach ($sents as $sent) {
            if (!preg_match('/\S/', $sent)) continue;
            $sent_array = array('src' => $sent);
            $tokens = tokenize_ml($sent, $tok_exc);
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
    if (!$text || !$book_id || !$par_num) return 0;
    //removing unicode diacritics
    $clear_text = '';
    for($i = 0; $i < mb_strlen($text, 'UTF-8'); ++$i) {
        $char = mb_substr($text, $i, 1, 'UTF-8');
        if (uniord($char) != 769) $clear_text .= $char;
    }
    $revset_id = create_revset();
    if (!$revset_id) return 0;
    $sent_count = 0;
    $pars = split2paragraphs($clear_text);
    foreach($pars as $par) {
        if (!preg_match('/\S/', $par)) continue;
        //adding a paragraph
        if (!sql_query("INSERT INTO `paragraphs` VALUES(NULL, '$book_id', '".($par_num++)."')")) return 0;
        $par_id = sql_insert_id();
        $sent_num = 1;
        $sents = split2sentences($par);
        foreach($sents as $sent) {
            if (!preg_match('/\S/', $sent)) continue;
            //adding a sentence
            if (!sql_query("INSERT INTO `sentences` VALUES(NULL, '$par_id', '".($sent_num++)."', '".mysql_real_escape_string(trim($sent))."', '0')")) return 0;
            $sent_id = sql_insert_id();
            $token_num = 1;
            $tokens = explode('^^', $sentences[$sent_count++]);
            foreach ($tokens as $token) {
                if (trim($token) === '') continue;
                //adding a textform
                if (!sql_query("INSERT INTO `text_forms` VALUES(NULL, '$sent_id', '".($token_num++)."', '".mysql_real_escape_string($token)."', '0')")) return 0;
                $tf_id = sql_insert_id();
                //adding a revision
                if (!sql_query("INSERT INTO `tf_revisions` VALUES(NULL, '$revset_id', '$tf_id', '".mysql_real_escape_string(generate_tf_rev($token))."')")) return 0;
            }
        }
    }
    return 1;
}
?>
