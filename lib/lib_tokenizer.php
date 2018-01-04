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

class Features {
    const CURRENT_CHAR_CLASS_1 = 0;
    const CURRENT_CHAR_CLASS_2 = 1;
    const CURRENT_CHAR_CLASS_3 = 2;
    const CURRENT_CHAR_CLASS_4 = 3;
    const NEXT_CHAR_CLASS_1 = 4;
    const NEXT_CHAR_CLASS_2 = 5;
    const NEXT_CHAR_CLASS_3 = 6;
    const NEXT_CHAR_CLASS_4 = 7;
    const PREV_CHAR_NUMBER = 8;
    const NEXT2_CHAR_NUMBER = 9;
    const WORD_FROM_DICT = 10;
    const HAS_SUFFIX = 11;
    const SAME_CHAR_AS_NEXT = 12;
    const LOOKS_LIKE_URL = 13;
    const IS_EXCEPTION = 14;
    const HAS_PREFIX = 15;
    const LOOKS_LIKE_TIME = 16;

    const MAX = self::LOOKS_LIKE_TIME;
}

class FeatureCalculator {
    private $prefixes;
    private $exceptions;

    private $values;

    public function __construct($prefix_list, $exception_list) {
        $this->prefixes = $prefix_list;
        $this->exceptions = $exception_list;
    }

    public function calc($text, $pos) {
        $this->values = array_fill(0, Features::MAX + 1, 0);
        $chars = array(
            -1 => $pos > 0 ? mb_substr($text, $pos-1, 1) : '',
            0 => mb_substr($text, $pos, 1),
            1 => mb_substr($text, $pos+1, 1),
            2 => mb_substr($text, $pos+2, 1)
        );

        $this->_calc_char_classes($chars);

        $seq = $this->_get_current_sequences($text, $pos, $chars);
        if ($seq['delimiter'] == '-') {
            $this->values[Features::WORD_FROM_DICT] = $this->_is_dictionary_word($seq['full']);
            $this->values[Features::HAS_PREFIX] = $this->_is_prefix($seq['left']);
            $this->values[Features::HAS_SUFFIX] = $this->_is_suffix($seq['right']);
        }
        elseif ($seq['delimiter'] !== '') {
            $this->values[Features::LOOKS_LIKE_URL] = looks_like_url($seq['full'], $seq['right']);
            $this->values[Features::IS_EXCEPTION] = $this->_is_exception($seq['full']);
        }

        if ($seq['delimiter'] == ':') {
            $this->values[Features::LOOKS_LIKE_TIME] = looks_like_time($seq['left'], $seq['right']);
        }

        return $this->values;
    }

    private function _calc_char_classes($chars) {
        list(
            $this->values[Features::CURRENT_CHAR_CLASS_1],
            $this->values[Features::CURRENT_CHAR_CLASS_2],
            $this->values[Features::CURRENT_CHAR_CLASS_3],
            $this->values[Features::CURRENT_CHAR_CLASS_4]
        ) = $this->_char_class($chars[0]);

        list(
            $this->values[Features::NEXT_CHAR_CLASS_1],
            $this->values[Features::NEXT_CHAR_CLASS_2],
            $this->values[Features::NEXT_CHAR_CLASS_3],
            $this->values[Features::NEXT_CHAR_CLASS_4]
        ) = $this->_char_class($chars[1]);

        $this->values[Features::PREV_CHAR_NUMBER] = is_number($chars[-1]);
        $this->values[Features::NEXT2_CHAR_NUMBER] = is_number($chars[2]);
        $this->values[Features::SAME_CHAR_AS_NEXT] = is_same_char($chars[0], $chars[1]);
    }

    private static function _char_class($char) {
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
            (is_colon($char)        ? '1100' :
            (is_pmark2($char)       ? '1101' : '0000'))))))))))));
        return array_map('intval', str_split($ret));
    }

    private static function _is_dictionary_word($word) {
        if ($word === "") return 0;
        return (int)(form_exists(mb_strtolower($word)) > 0);
    }

    private static function _is_suffix($s) {
        return (int)in_array($s, array('то', 'таки', 'с', 'ка', 'де'));
    }

    private function _is_exception($s) {
        $s = mb_strtolower($s);
        if (in_array($s, $this->exceptions))
            return 1;
        if (!preg_match('/^\W|\W$/u', $s))
            return 0;
        $s = preg_replace('/^[^A-Za-zА-ЯЁа-яё0-9]+/u', '', $s);
        if (in_array($s, $this->exceptions))
            return 1;
        while (preg_match('/[^A-Za-zА-Яа-яЁё0-9]$/u', $s)) {
            $s = preg_replace('/[^A-Za-zА-ЯЁа-яё0-9]$/u', '', $s);
            if (in_array($s, $this->exceptions))
                return 1;
        }
        return 0;
    }
    private function _is_prefix($s) {
        return in_array(mb_strtolower($s), $this->prefixes) ? 1 : 0;
    }

    private static function _get_current_sequences($text, $pos, $chars) {
        $chain_left = $chain_right = '';
        $odd_symbol = '';
        if (is_hyphen($chars[0]) || is_hyphen($chars[1])) {
            $odd_symbol = '-';
        }
        elseif (preg_match('/([\.\/\?\=\:&"!\+\(\)])/u', $chars[0], $match) || preg_match('/([\.\/\?\=\:&"!\+\(\)])/u', $chars[1], $match)) {
            $odd_symbol = $match[1];
        }
        if ($odd_symbol) {
            for ($j = $pos; $j >= 0; --$j) {
                $t = mb_substr($text, $j, 1);
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
            for ($j = $pos+1; $j < mb_strlen($text); ++$j) {
                $t = mb_substr($text, $j, 1);
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
        }
        return array(
            'left' => $chain_left,
            'right' => $chain_right,
            'delimiter' => $odd_symbol,
            'full' => $chain_left.$odd_symbol.$chain_right
        );
    }
}

class TokenInfo {
    public $text;
    public $start_pos;
    public $end_pos;
    public $border_weight;
    private $features;

    public function __construct($text, $start_pos, $end_pos, $weight, $fs) {
        $this->text = $text;
        $this->start_pos = $start_pos;
        $this->end_pos = $end_pos;
        $this->border_weight = $weight;
        $this->features = $fs;
    }

    public function get_feats_str_binary() {
        return implode('', $this->features);
    }

    public function get_feats_str_decimal() {
        return bindec($this->get_feats_str_binary());
    }
}

class Tokenizer {
    private $files_dir;
    private $bad_sentences;

    private $stats;  // dict `feat vector' => [cases_with_border, total_cases, ratio]
    private $oddity;  // dict `feat_vector'#{0,1} => ratio

    private $feat_calcer;

    public function __construct($aux_files_dir) {
        $this->files_dir = $aux_files_dir;
        $exceptions = array_map('mb_strtolower', $this->_readfile('tokenizer_exceptions.txt'));
        $prefixes = $this->_readfile('tokenizer_prefixes.txt');
        $this->bad_sentences = array_map('intval', array_values(preg_grep('/^[0-9]+$/', $this->_readfile('bad_sentences.txt'))));

        $this->stats = array();
        $this->_read_stats();

        $this->oddity = array();

        $this->feat_calcer = new FeatureCalculator($prefixes, $exceptions);
    }

    public function train() {
        $this->stats = array();  // forget what was read
        sql_begin();
        $this->_clear_db();
        $this->_train(1);
        $this->_train(2);
        sql_commit();
    }

    public function tokenize($text, $min_weight=0.0) {
        $out = array();
        $token = '';

        $text = $this->_prepare_text($text);

        $text .= '  ';  // for features about following characters to work at end

        for ($i = 0; $i < mb_strlen($text); ++$i) {
            $vector = $this->feat_calcer->calc($text, $i);
            $key = $this->_feats_as_string($vector);

            if (isset($this->stats[$key])) {
                $sum = $this->stats[$key][2];
            } else {
                $sum = 0.5;
            }

            $token .= mb_substr($text, $i, 1);

            if ($sum > $min_weight) {
                $token = trim($token);
                $start_pos = $i - mb_strlen($token) + 1;
                if ($token !== '') $out[] = new TokenInfo($token, $start_pos, $i, $sum, $vector);
                $token = '';
            }
        }
        return $out;
    }

    private function _read_stats() {
        $res = sql_query("SELECT * FROM tokenizer_coeff");
        while ($r = sql_fetch_array($res)) {
            $this->stats[$r[0]] = array(0, 0, $r[1]);
        }
    }

    private function _get_features_vector($text, $pos) {
        // returns vector of 0's and 1's
        return $this->feat_calcer->calc($text, $pos);
    }

    private function _train($pass) {
        // 2 passes: 1st pass: calculate stats, 2nd pass: save strange cases
        foreach ($this->_get_training_sentences() as $sentence) {
            $text = $this->_prepare_text($sentence['text']);
            $border_pos = $this->_get_border_positions($text, $sentence['tokens'], $pass == 2);
            if (!sizeof($border_pos))
                continue;
            for ($i = 0; $i < mb_strlen($text); ++$i) {
                $fs = $this->_get_features_vector($text, $i);
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
        sql_query("DELETE FROM tokenizer_coeff");
        sql_query("DELETE FROM tokenizer_strange");  // DELETE instead of TRUNCATE for atomicity
        sql_query("DELETE FROM stats_values WHERE param_id = " . STATS_BROKEN_TOKEN_IDS);
    }

    private function _get_training_sentences() {
        $res = sql_query("
            SELECT sent_id, source, tf_id, tf_text
            FROM sentences
            LEFT JOIN tokens USING (sent_id)
            ORDER BY sent_id, tokens.pos
        ");
        $sentence = array('tokens' => array(), 'id' => 0, 'text' => '');
        while ($r = sql_fetch_array($res)) {
            if ($r['sent_id'] != $sentence['id']) {
                if ($sentence['id']) {
                    if (!in_array((int)$sentence['id'], $this->bad_sentences)) {
                        yield $sentence;
                    }
                    $sentence['tokens'] = array();
                }
                $sentence['id'] = $r['sent_id'];
                $sentence['text'] = $r['source'];
            }
            $sentence['tokens'][] = array('id' => $r['tf_id'], 'text' => $r['tf_text']);
        }

        if (sizeof($sentence['tokens']) && !in_array((int)$sentence['id'], $this->bad_sentences)) {
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
                        self::_save_broken_token($token['id']);
                    return array();
                }
            }
            $borders[] = $pos + $token_len - 1;
            $pos += $token_len;
        }

        return $borders;
    }

    private static function _save_broken_token($token_id) {
        self::_add_stats_value(STATS_BROKEN_TOKEN_IDS, $token_id);
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
        self::_add_stats_value(STATS_TOKENIZER_SURE_RATIO, intval($sure_cases / $all_cases * 100000));
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
        if (sizeof(array_unique($feat_vector)) > 2)
            throw new Exception("expected a binary vector");
        return bindec(implode('', $feat_vector));
    }
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
function is_colon($char) {
    return (int)($char == ':');
}
function is_single_quote($char) {
    return (int)($char == "'");
}
function is_same_char($char1, $char2) {
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
    $re_punctuation = '/[,;"\xAB\xBB]/u';
    return preg_match($re_punctuation, $char);
}
function is_pmark2($char) {
    return (int)($char == '?' || $char == '!');
}
function is_bracket1($char) {
    $re_bracket = '/[\(\[\{\<]/u';
    return preg_match($re_bracket, $char);
}
function is_bracket2($char) {
    $re_bracket = '/[\)\]\}\>]/u';
    return preg_match($re_bracket, $char);
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
    if ($right === '')
        return 0;
    $left = preg_replace('/^[^0-9]+/u', '', $left);
    $right = preg_replace('/[^0-9]+$/u', '', $right);

    if (!preg_match('/^[0-9][0-9]?$/u', $left) || !preg_match('/^[0-9][0-9]$/u', $right))
        return 0;

    if ($left < 24 && $right < 60)
        return 1;

    return 0;
}

function remove_bad_symbols($text) {
    $clear_text = '';
    for ($i = 0; $i < mb_strlen($text); ++$i) {
        $char = mb_substr($text, $i, 1);
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
    return $clear_text;
}

function addtext_check($txt, $book_id) {
    global $config;

    check_permission(PERM_ADDER);

    $clear_text = remove_bad_symbols($txt);

    $out = array('full' => $clear_text, 'select0' => get_books_for_select(0));
    $tokenizer = new Tokenizer(__DIR__ . '/../scripts/tokenizer');
    $pars = split2paragraphs($clear_text);
    foreach ($pars as $par) {
        if (!preg_match('/\S/', $par)) continue;
        $par_array = array();
        $sents = split2sentences($par);
        foreach ($sents as $sent) {
            if (!preg_match('/\S/', $sent)) continue;
            $sent_array = array('src' => $sent);
            $tokens = $tokenizer->tokenize($sent);
            foreach ($tokens as $token) {
                $sent_array['tokens'][] = array(
                    'text' => $token->text,
                    'class' => form_exists($token->text),
                    'border' => $token->border_weight,
                    'vector' => $token->get_feats_str_binary() . '=' . $token->get_feats_str_decimal());
            }
            $par_array['sentences'][] = $sent_array;
        }
        $out['paragraphs'][] = $par_array;
    }
    //book
    if ($book_id) {
        $book_id = (int)$book_id;
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
