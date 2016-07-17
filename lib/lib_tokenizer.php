<?php
require_once('constants.php');
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

class Tokenizer {
    private $files_dir;
    private $exceptions;
    private $bad_sentences;
    private $prefixes;

    private $stats;  // dict `feat vector' => [cases_with_border, total_cases, ratio]
    private $oddity;  // dict `feat_vector'#{0,1} => ratio

    public function __construct($aux_files_dir) {
        $this->files_dir = $aux_files_dir;
        $this->exceptions = array_map('mb_strtolower', $this->_readfile('tokenizer_exceptions.txt'));
        $this->prefixes = $this->_readfile('tokenizer_prefixes.txt');
        $this->bad_sentences = array_map('intval', preg_grep('/^[0-9]+$/', $this->_readfile('bad_sentences.txt')));

        $this->stats = array();
        $this->oddity = array();
    }

    public function train() {
        sql_begin();
        $this->_clear_db();
        $this->_train(1);
        $this->_train(2);
        sql_commit();
    }

    public static function get_features_vector($text, $pos) {
        // returns vector of 0's and 1's
        return array();
        // TODO
    }

    private function _train($pass) {
        // 2 passes: 1st pass: calculate stats, 2nd pass: save strange cases
        foreach ($this->_get_training_sentences() as $sentence) {
            $text = $this->_prepare_text($sentence['text']);
            $border_pos = $this->_get_border_positions($text, $sentence['tokens'], $pass == 2);
            if (!sizeof($border_pos))
                continue;
            for ($i = 0; $i < mb_strlen($text); ++$i) {
                $fs = $this->get_features_vector($text, $i);
                $is_border = in_array($i, $border_pos);
                switch ($pass) {
                case 1:
                    $this->_update_stats($fs, $is_border);
                    break;
                case 2:
                    if ($oddity = $this->_get_oddity($fs, $is_border)) {
                        $this->_save_odd_case($sentence['id'], $i, $is_border, $oddity);
                    }
                    break;
                default:
                    throw new Exception("Incorrect pass number");
                }
            }
        }
        if ($pass == 1) {
            $this->_save_stats();
            $this->_calculate_oddity();
        }
    }

    private function _readfile($name) {
        return file($this->files_dir . '/' . $name, FILE_IGNORE_NEW_LINES);
    }

    private function _clear_db() {
        sql_query("TRUNCATE TABLE tokenizer_coeff");
        sql_query("TRUNCATE TABLE tokenizer_strange");
        sql_query("DELETE FROM stats_values WHERE param_id = " . STATS_BROKEN_TOKEN_IDS);
    }

    private function _get_training_sentences() {
        $res = sql_query("
            SELECT sent_id, source, tf_id, tf_text
            FROM sentences
            LEFT JOIN tokens USING (sent_id)
            WHERE par_id < 20
            ORDER BY sent_id, tokens.pos
        ");
        $sentence = array('tokens' => array(), 'id' => 0, 'text' => '');
        while ($r = sql_fetch_array($res)) {
            if ($r['sent_id'] != $sentence['id']) {
                if ($sentence['id']) {
                    if (!in_array($r['sent_id'], $this->bad_sentences)) {
                        yield $sentence;
                    }
                    $sentence['tokens'] = array();
                }
                $sentence['id'] = $r['sent_id'];
                $sentence['text'] = $r['source'];
            }
            $sentence['tokens'][] = array('id' => $r['tf_id'], 'text' => $r['tf_text']);
        }

        if (sizeof($sentence['tokens']) && !in_array($sentence['id'], $this->bad_sentences)) {
            yield $sentence;
        }
    }

    private static function _prepare_text($text) {
        return Normalizer::normalize($text, Normalizer::FORM_C);
    }

    private static function _get_border_positions($text, $tokens, $save_broken_tokens=true) {
        // returns list of positions (0-based int)
        $borders = array();
        $pos = 0;
        foreach ($tokens as $token) {
            $token_len = mb_strlen($token['text']);
            while (mb_substr($text, $pos, $token_len) !== $token['text']) {
                if (++$pos > mb_strlen($text)) {
                    if ($save_broken_tokens)
                        $this->_save_broken_token($token['id']);
                    return array();
                }
            }
            $borders[] = $pos + $token_len - 1;
            $pos += $token_len;
        }

        return $borders;
    }

    private static function _save_broken_token($token_id) {
        $this->_add_stats_value(STATS_BROKEN_TOKEN_IDS, $token_id);
    }

    private function _update_stats($feat_vector, $is_border) {
        $key = $this->_feats_as_string($feat_vector);
        if (!isset($this->stats[$key])) {
            $this->stats[$key] = array(0, 0, 0);
        }
        $this->stats[$key][1] += 1;
        if ($is_border)
            $this->stats[$key][0] += 1;
    }

    private function _calculate_oddity() {
        $sure_cases = 0;
        $all_cases = 0;
        foreach ($this->stats as $feat_str => $data) {
            $ratio = $data[2];

            $all_cases += $data[1];
            if ($ratio == 0 || $ratio == 1) {
                $sure_cases += $data[1];
            } else {
                $key = $feat_str . '#' . ($ratio > 0.5 ? '0' : '1');
                $this->oddity[$key] = $ratio > 0.5 ? $ratio : (1 - $ratio);
            }
        }
        $this->_add_stats_value(STATS_TOKENIZER_SURE_RATIO, intval($sure_cases / $all_cases * 100000));
    }

    private static function _add_stats_value($param, $value) {
        sql_pe("INSERT INTO stats_values (timestamp, param_id, param_value) VALUES (?, ?, ?)",
            array(time(), $param, $value));
    }

    private function _save_stats() {
        foreach ($this->stats as $feat_str => &$data) {
            $data[2] = $data[0] / $data[1];
            sql_pe("INSERT INTO tokenizer_coeff VALUES (?, ?)", array($feat_str, $data[2]));
        }
    }

    private function _get_oddity($feat_vector, $is_border) {
        $key = $this->_feats_as_string($feat_vector) . '#' . (int)$is_border;
        if (isset($this->oddity[$key])) {
            return $this->oddity[$key];
        }
    }

    private function _save_odd_case($sent_id, $pos, $is_border, $oddity) {
        sql_pe("INSERT INTO tokenizer_strange VALUES (?, ?, ?, ?)",
            array($sent_id, $pos, $is_border ? 1 : 0, $oddity));
    }

    private function _feats_as_string($feat_vector) {
        assert(sizeof(array_unique($feat_vector)) <= 2);
        return bindec(implode('', $feat_vector));
    }
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

    for ($i = 0; $i < mb_strlen($txt); ++$i) {
        $prevchar  = ($i > 0 ? mb_substr($txt, $i-1, 1) : '');
        $char      =           mb_substr($txt, $i+0, 1);
        $nextchar  =           mb_substr($txt, $i+1, 1);
        $nnextchar =           mb_substr($txt, $i+2, 1);

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
                $t = mb_substr($txt, $j, 1);
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
            for ($j = $i+1; $j < mb_strlen($txt); ++$j) {
                $t = mb_substr($txt, $j, 1);
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
    return (int)(form_exists(mb_strtolower($chain)) > 0);
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
    if (in_array(mb_strtolower($s), $prefixes))
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
    for ($i = 0; $i < mb_strlen($array['txt']); ++$i) {
        $char = mb_substr($array['txt'], $i, 1);
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
    if (sizeof(sql_pe("SELECT book_id FROM books WHERE parent_id=?", array($book_id))) > 0)
        throw new UnexpectedValueException("Can't add paragraphs to a text having subtexts");
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
