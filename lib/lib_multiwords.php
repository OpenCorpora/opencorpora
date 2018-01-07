<?php
require_once('lib_annot.php');

class MultiWordTask {

    const ANSWERS_PER_TASK = 4;
    const CONTEXT_WIDTH = 5;

    const NOT_READY        = 0;
    const READY            = 1;
    const APPROVED_AUTO    = 2;
    const REJECTED_AUTO    = 3;
    const APPROVED_MANUAL  = 4;
    const REJECTED_MANUAL  = 5;

    const NOT_APPLIED    = 0;
    const APPLIED_AUTO   = 1;
    const APPLIED_MANUAL = 2;

    const ANSWER_YES  = 1;
    const ANSWER_NO   = 2;
    const ANSWER_SKIP = 3;

    public static $CAPTIONS = array(
        0 => 'Мультитокен',
        1 => 'Сложный предлог',
        2 => 'Сложный союз'
    );
    

    public static function get_tasks($user_id, $num=1) {
        $res = sql_pe("
            SELECT mw_id, tf_id, sent_id, mw_type
            FROM mw_main
            JOIN mw_tokens mwt USING (mw_id)
            LEFT JOIN tokens t USING (tf_id)
            WHERE status = ". self::NOT_READY ."
            AND mw_id NOT IN (
                SELECT DISTINCT mw_id
                FROM mw_answers
                LEFT JOIN mw_main USING (mw_id)
                WHERE user_id = ?
                AND status = ". self::NOT_READY ."
            )
            ORDER BY sent_id, mw_id
            LIMIT ?
        ", array($user_id, $num * 10));

        if (!sizeof($res))
            return array();

        $out = array();

        $sent_id = $res[0]['sent_id'];
        $mw_type = $res[0]['mw_type'];
        $mw_id = $res[0]['mw_id'];
        $token_ids = array();
        foreach ($res as $row) {
            if ($mw_id != $row['mw_id']) {
                if (sizeof($token_ids) < 2)
                    throw new Exception("Too few tokens");

                $out[] = array(
                    'id' => $mw_id,
                    'token_ids' => $token_ids,
                    'context' => get_context_for_word($token_ids[0], sizeof($token_ids) + self::CONTEXT_WIDTH),
                    'caption' => self::$CAPTIONS[$mw_type]
                );
                if (sizeof($out) == $num)
                    break;

                $token_ids = array();
                $sent_id = $row['sent_id'];
                $mw_id = $row['mw_id'];
                $mw_type = $row['mw_type'];
            }
            if ($sent_id != $row['sent_id'])
                throw new Exception("Inconsistent sentence id");
            $token_ids[] = $row['tf_id'];
        }

        return $out;
    }

    public static function register_answer($mw_id, $user_id, $answer) {
        if (!$mw_id || !$user_id || !in_array($answer, array(self::ANSWER_YES, self::ANSWER_NO, self::ANSWER_SKIP)))
            throw new UnexpectedValueException();
        sql_begin();
        sql_pe("REPLACE INTO mw_answers (mw_id, user_id, answer) VALUES (?, ?, ?)", array($mw_id, $user_id, $answer));
        self::_check_mw_status($mw_id);
        sql_commit();
    }

    private static function _check_mw_status($mw_id) {
        $res = sql_pe("
            SELECT COUNT(DISTINCT user_id) As cnt
            FROM mw_answers
            WHERE mw_id = ?
            AND answer in (".self::ANSWER_YES.", ".self::ANSWER_NO.")
        ", array($mw_id));

        if ($res[0]['cnt'] >= self::ANSWERS_PER_TASK)
            sql_pe("UPDATE mw_main SET status = ? WHERE mw_id = ? LIMIT 1", array(self::READY, $mw_id));
    }
}

class FoundTokens {
    public $tokens;
    public $type;

    public function __construct($type, $tokens) {
        $this->type = $type;
        $this->tokens = $tokens;
    }
}

class MultiWordSearchRule {
    
    const EXACT_FORM = 0;

    private $tokens = array();
    private $t_index;
    private $mw_type = 0;

    public function __construct($line) {
        $parts = explode('@', $line);
        if (sizeof($parts) > 1) {
            $this->mw_type = intval(trim($parts[1]));
        }

        foreach (explode(' ', trim($parts[0])) as $t) {
            $this->tokens[] = array($t, self::EXACT_FORM);
        }
        $this->t_index = array_unique(array_column($this->tokens, 0));
        usort($this->t_index, array("MultiWordSearchRule", "cmp_tokens"));
    }
    
    public static function cmp_tokens($a, $b) {
        if (mb_strlen($a) == mb_strlen($b)) {
            $punct_a = ctype_punct($a);
            $punct_b = ctype_punct($b);
            if ($punct_a == $punct_b)
                return 0;
            else
                return $punct_a > $punct_b ? -1 : 1;
        }
        else
            return mb_strlen($a) > mb_strlen($b) ? -1 : 1;
    }

    // returns array of FoundTokens() objects
    public function do_search() {
        echo "searching for " . implode(" ", array_column($this->tokens, 0)) . "\n";
        $found = array();
        // get candidates = all tokens from sentences, containing all required tokens
        $res = sql_query($this->_construct_query());
        $sent_id = 0;
        $sentence = array();
        $sents = array();
        while ($row = sql_fetch_array($res)) {
            if ($row["sent_id"] != $sent_id ) {
                if (!empty($sentence)) {
                    $sets = $this->_filter_sentence($sentence);
                    foreach ($sets as $set) {
                        $found[] = new FoundTokens($this->mw_type, $set);
                    }
                    $sentence = array();
                }
                $sent_id = $row["sent_id"];
            }
            $sentence[] = $row;
        }

        $sets = $this->_filter_sentence($sentence);
        foreach ($sets as $set) {
            $found[] = new FoundTokens($this->mw_type, $set);
        }

        echo sizeof($found) . " candidates found\n";
        return $found;
    }

    private function _construct_query() {
        $query = "SELECT * FROM tokens WHERE sent_id IN (";
        $cnt = 0;
        foreach ($this->t_index as $token) {
            $cnt += 1;
            $query .= 'SELECT sent_id FROM tokens WHERE tf_text = "' . $token . '"';
            if ($cnt < sizeof($this->t_index)) {
                $query .= " AND sent_id IN (";
            }
        }
        $query .= str_repeat(")", sizeof($this->t_index));
        $query .= " ORDER BY sent_id, pos";
        #$query .= " LIMIT 100";
        return $query;
    }

    private function _match_token($token, $idx) {
        switch ($this->tokens[$idx][1]) {
            case self::EXACT_FORM:
                return mb_convert_case($token["tf_text"], MB_CASE_LOWER) == $this->tokens[$idx][0];
            default:
                throw new UnexpectedValueException("Unknown matching type");
        }
    }

    // gets array of arrays of matching tokens in a sentence
    private function _filter_sentence($sent) {
        $mwords = array();
        $mword = array();
        $tnum = 0;
        foreach ($sent as $token) {
            if ($this->_match_token($token, $tnum)) {
                $mword[] = $token["tf_id"];
                if ($tnum == sizeof($this->tokens) - 1) {
                    // match - last token found
                    $mwords[] = $mword;
                    $mword = array();
                    $tnum = 0;
                }
                else {
                    // continue matching
                    $tnum += 1;
                }
            }
            else {
                // continue searching
                $tnum = 0;
                $mword = array();
            }
        }
        return $mwords;
    }
}

class MultiWordFinder {
    private $rules;

    public function __construct($rules_file, $limit = array()) {
        $this->_parse_rules(file($rules_file), $limit);
    }

    public function find() {
        if (empty($this->rules))
            die("No rules passed\n");
        $found = array();
        foreach ($this->rules as $rule) {
            $found = array_merge($found, $rule->do_search());
        }
        $found = $this->_remove_existing_token_sets($found);
        $this->_save_found_tokens($found);
    }

    private function _parse_rules($lines, $limit) {
        foreach ($lines as $i => $line) {
            if (!empty($limit) && !in_array($i, $limit))
                continue;
            $line = trim($line);
            if (!$line || $line[0] == '#')
                continue;
            $this->rules[] = new MultiWordSearchRule($line);
        }
    }

    private function _remove_existing_token_sets($token_sets) {
        // create an index of existing multiwords (sets indexed by the first token)
        echo "searching for duplicates... ";
        $res = sql_query("SELECT * FROM mw_tokens LEFT JOIN tokens USING (tf_id) ORDER BY mw_id, pos");
        $mw_index = array();
        $mw = array();
        $mw_id = 0;
        while ($row = sql_fetch_array($res)) {
            if ($row["mw_id"] != $mw_id) {
                if (!empty($mw)) {
                    if (!array_key_exists($mw[0], $mw_index))
                        $mw_index[$mw[0]] = array();
                    $mw_index[$mw[0]][] = $mw;
                    $mw = array();
                }
                $mw_id = $row["mw_id"];
            }
            $mw[] = $row["tf_id"];
        }
        if (!array_key_exists($mw[0], $mw_index))
            $mw_index[$mw[0]] = array();
        $mw_index[$mw[0]][] = $mw;
        // filter found multiwords
        foreach ($token_sets as $i => $set) {
            if (!$set instanceof FoundTokens)
                throw new Exception();
            if (array_key_exists($set->tokens[0], $mw_index)) {
                if (in_array($set->tokens, $mw_index[$set->tokens[0]])) {
                    unset($token_sets[$i]);
                }
            }
        }
        echo sizeof($token_sets) . " candidates left\n";
        return $token_sets;
    }

    private function _save_found_tokens($token_sets) {
        sql_begin();
        foreach ($token_sets as $tset) {
            if (!$tset instanceof FoundTokens)
                throw new Exception();
            sql_pe("INSERT INTO mw_main (status, applied, mw_type) VALUES (?, ?, ?)",
                   array(MultiWordTask::NOT_READY, MultiWordTask::NOT_APPLIED, $tset->type));
            $mw_id = sql_insert_id();
            foreach ($tset as $tf_id) {
                sql_pe("INSERT INTO mw_tokens (mw_id, tf_id) VALUES (?, ?)", array($mw_id, $tf_id));
            }
        }
        sql_commit();
    }
}

?>
