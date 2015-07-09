<?php

class MorphParse {
    public $lemma_id = 0;
    public $lemma_text;
    public $gramlist = array();
    // $gramlist is array with keys 'inner', 'outer', 'descr'

    public function __construct($lemma_text = "", $gramlist = array(), $lemma_id = 0) {
        $this->lemma_id = $lemma_id;
        $this->lemma_text = $lemma_text;
        $this->gramlist = $gramlist;
    }

    public function from_xml_ary($xml_arr) {
        $lemma_grm = $xml_arr['_c']['l']['_c']['g'];
        $grm_arr = array();
        if (isset ($lemma_grm['_a']) && is_array($lemma_grm['_a'])) {
            $grm_arr[] = array('inner' => $lemma_grm['_a']['v']);
        } elseif (is_array($lemma_grm)) {
            foreach ($lemma_grm as $t) {
                $grm_arr[] = array('inner' => $t['_a']['v']);
            }
        }
        $this->lemma_id   = $xml_arr['_c']['l']['_a']['id'];
        $this->lemma_text = $xml_arr['_c']['l']['_a']['t'];
        $this->gramlist  = $grm_arr;
    }

    public function replace_gram_subset($gram_find, $gram_replace) {
        // bad but quick
        $gram_find_str = join(':', $gram_find);
        $gram_replace_str = join(':', $gram_replace);
        $old_gram_str = join(':', array_map(function($a) { return $a['inner']; }, $this->gramlist));
        $gramlist_inner = explode(':', str_replace($gram_find_str, $gram_replace_str, $old_gram_str));
        // additional info (outer, descr) is lost, seems ok
        $this->gramlist = array();
        foreach ($gramlist_inner as $gr)
            $this->gramlist[] = array('inner' => $gr);
    }

    public function to_xml() {
        $out = '<v><l id="'.$this->lemma_id.'" t="'.$this->lemma_text.'">';
        foreach ($this->gramlist as $gram)
            $out .= '<g v="'.$gram['inner'].'"/>';
        $out .= '</l></v>';
        return $out;
    }
}

class MorphParseSet {
    public $token_text;
    public $parses;
    private static $gram_descr = array();

    public function __construct($xml="", $token_text="", $force_unknown=false, $force_include_init=false) {
        if ($xml)
            $this->_from_xml($xml);
        elseif ($token_text)
            $this->_from_token($token_text, $force_unknown, $force_include_init);
        else
            throw new Exception();
    }

    public function to_xml() {
        $out = '<tfr t="'.htmlspecialchars($this->token_text).'">';
        foreach ($this->parses as $parse)
            $out .= $parse->to_xml();
        $out .= '</tfr>';
        return $out;
    }

    public function filter_by_lemma($lemma_id, $allow) {
        $newparses = array();
        foreach ($this->parses as $parse)
            if (($parse->lemma_id == $lemma_id) == $allow)
                $newparses[] = $parse;
        $this->parses = $newparses;
        if (sizeof($this->parses) == 0)
            $this->_from_token($this->token_text, true, false);
    }

    public function filter_by_parse_index($index_array) {
        $newparses = array();
        foreach ($this->parses as $i => $parse)
            if (in_array($i, $index_array))
                $newparses[] = $parse;
        $this->parses = $newparses;
        if (sizeof($this->parses) == 0)
            $this->_from_token($this->token_text, true, false);
    }

    public function set_lemma_text($lemma_id, $lemma_text) {
        if (!$lemma_id || !$lemma_text)
            throw new Exception();
        foreach ($this->parses as $parse)
            if ($parse->lemma_id == $lemma_id)
                $parse->lemma_text = $lemma_text;
    }

    public function replace_gram_subset($lemma_id, $gram_find, $gram_replace) {
        if (!$lemma_id || !is_array($gram_find) || !is_array($gram_replace))
            throw new Exception();
        foreach ($this->parses as $parse)
            if ($parse->lemma_id == $lemma_id)
                $parse->replace_gram_subset($gram_find, $gram_replace);
    }

    private static function _fill_gram_info($gram_list) {
        // TODO preload all the grammemes at once and cache
        $t = array();
        foreach ($gram_list as $gr) {
            if (!isset(self::$gram_descr[$gr['inner']])) {
                $r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='".$gr['inner']."' COLLATE utf8_bin LIMIT 1"));
                self::$gram_descr[$gr['inner']] = array($r[0], $r[1]);
            }
            $t[] = array(
                'inner' => $gr['inner'],
                'outer' => self::$gram_descr[$gr['inner']][0],
                'descr' => self::$gram_descr[$gr['inner']][1]
            );
        }
        return $t;
    }

    private static function _yo_filter($token, $arr) {
        $token = mb_strtolower($token);

        if (!preg_match('/ё/u', $token))
            return $arr;

        // so there is a 'ё'
        $res = sql_pe("
            SELECT lemma_id, lemma_text, grammems
            FROM form2lemma
            WHERE form_text COLLATE 'utf8_bin' = ?
            ORDER BY lemma_id, grammems
        ", array($token));
        // return if no difference
        if (sizeof($res) == sizeof($arr) || !sizeof($res))
            return $arr;

        // otherwise the difference is what we need to omit
        $out = array();
        foreach ($res as $r)
            $out[] = $r;
        return $out;
    }

    private function _from_xml($xml) {
        $arr = xml2ary($xml);
        $this->token_text = $arr['tfr']['_a']['t'];
        $xml_arr = $arr['tfr']['_c']['v'];
        if (isset($xml_arr['_c']) && is_array($xml_arr['_c'])) {
            //the only variant
            $this->parses[] = new MorphParse();
            $this->parses[0]->from_xml_ary($xml_arr);
            $this->parses[0]->gramlist = self::_fill_gram_info($this->parses[0]->gramlist);
        } elseif (is_array($xml_arr)) {
            //multiple variants
            foreach ($xml_arr as $i => $xml_var_arr) {
                $this->parses[] = new MorphParse();
                $this->parses[$i]->from_xml_ary($xml_var_arr);
                $this->parses[$i]->gramlist = self::_fill_gram_info($this->parses[$i]->gramlist);
            }
        }
        else
            throw new Exception();
    }

    private function _from_token($token, $force_unknown, $force_include_init) {
        $this->token_text = $token;
        if ($force_unknown) {
            $this->parses[] = new MorphParse($token, array(array('inner' => 'UNKN')));
        } elseif (preg_match('/^[А-Яа-яЁё][А-Яа-яЁё\-\']*$/u', $token)) {
            $res = sql_pe("
                SELECT lemma_id, lemma_text, grammems
                FROM form2lemma
                WHERE form_text=?
                ORDER BY lemma_id, grammems
            ", array($token));
            if (sizeof($res) > 0) {
                $var = array();
                foreach ($res as $r) {
                    $var[] = $r;
                }
                if (sizeof($var) > 1) {
                    $var = self::_yo_filter($token, $var);
                }
                foreach ($var as $r) {
                    $gramlist = array();
                    if (preg_match_all('/g v="([^"]+)"/', $r['grammems'], $matches) > 0) {
                        $require_uc = false;
                        foreach ($matches[1] as $gr) {
                            $gramlist[] = array('inner' => $gr);
                            if ($gr == 'Init')
                                $require_uc = true;
                        }
                    }
                    if (!$require_uc || $force_include_init || preg_match('/^[А-ЯЁ]+$/u', $token))
                        $this->parses[] = new MorphParse($r['lemma_text'], $gramlist, $r['lemma_id']);
                }
            } else {
                $this->parses[] = new MorphParse(mb_strtolower($token, 'UTF-8'), array(array('inner' => 'UNKN')));
            }
        } elseif (preg_match('/^\p{P}+$/u', $token)) {
            $this->parses[] = new MorphParse($token, array(array('inner' => 'PNCT')));
        } elseif (preg_match('/^\p{Nd}+[\.,]?\p{Nd}*$/u', $token)) {
            $this->parses[] = new MorphParse($token, array(array('inner' => 'NUMB')));
        } elseif (preg_match('/^[\p{Latin}\.-]+$/u', $token)) {
            $this->parses[] = new MorphParse($token, array(array('inner' => 'LATN')));
            if (preg_match('/^[IVXLCMDivxlcmd]+$/u', $token))
                $this->parses[] = new MorphParse($token, array(array('inner' => 'ROMN')));
        }

        if (sizeof($this->parses) == 0) {
            $this->parses[] = new MorphParse($token, array(array('inner' => 'UNKN')));
        }

        foreach ($this->parses as $parse)
            $parse->gramlist = self::_fill_gram_info($parse->gramlist);
    }
}

function get_sentence($sent_id) {
    $r = sql_fetch_array(sql_query("SELECT `check_status`, source FROM sentences WHERE sent_id=$sent_id LIMIT 1"));
    $out = array(
        'id' => $sent_id,
        'next_id' => get_next_sentence_id($sent_id),
        'prev_id' => get_previous_sentence_id($sent_id),
        'status' => $r['check_status'],
        'source' => $r['source']
    );
    //counting comments
    $r = sql_fetch_array(sql_query("SELECT COUNT(comment_id) comm_cnt FROM sentence_comments WHERE sent_id=$sent_id"));
    $out['comment_count'] = $r['comm_cnt'];
    //looking for source name
    $r = sql_fetch_array(sql_query("
        SELECT book_id, old_syntax_moder_id
        FROM books
        WHERE book_id = (
            SELECT book_id
            FROM paragraphs
            WHERE par_id = (
                SELECT par_id
                FROM sentences
                WHERE sent_id=$sent_id
                LIMIT 1
            )
        )
    "));
    $out['book_id'] = $book_id = $r['book_id'];
    $out['syntax_moder_id'] = $r['old_syntax_moder_id'];
    $r = sql_fetch_array(sql_query("
        SELECT book_name
        FROM books
        WHERE book_id = (
            SELECT parent_id
            FROM books
            WHERE book_id = $book_id
            LIMIT 1
        )
    "));
    $out['book_name'] = $r['book_name'];
    //looking for url
    $res = sql_query("
        SELECT tag_name
        FROM book_tags
        WHERE book_id = ".$book_id
    );
    while ($r = sql_fetch_array($res)) {
        if (substr($r['tag_name'], 0, 4) == 'url:') {
            $out['url'] = substr($r['tag_name'], 4);
            break;
        }
    }
    $tf_text = array();
    // TODO we'd better preload all grammemes info to save queries
    $res = sql_query("
        SELECT tf_id, tf_text, rev_text
        FROM tokens
        LEFT JOIN tf_revisions
            USING (tf_id)
        WHERE sent_id=$sent_id
        AND is_last = 1
        ORDER BY `pos`
    ");
    $j = 0; //token position, for further highlighting
    while ($r = sql_fetch_array($res)) {
        array_push($tf_text, '<span id="src_token_'.($j++).'">'.htmlspecialchars($r['tf_text']).'</span>');
        $parse = new MorphParseSet($r['rev_text']);
        $out['tokens'][] = array(
            'tf_id'        => $r['tf_id'],
            'tf_text'      => $r['tf_text'],
            'variants'     => $parse->parses
        );
    }
    $out['fulltext'] = typo_spaces(implode(' ', $tf_text), 1);
    return $out;
}
function get_previous_sentence_id($sent_id) {
    return get_adjacent_sentence_id($sent_id, false);
}
function get_next_sentence_id($sent_id) {
    return get_adjacent_sentence_id($sent_id, true);
}
function get_adjacent_sentence_id($sent_id, $next) {
    // same paragraph
    $r = sql_fetch_array(sql_query("
        SELECT par_id, pos
        FROM sentences
        WHERE sent_id = $sent_id LIMIT 1
    "));

    $par_id = $r['par_id'];
    $sent_pos = $r['pos'];
    if (!$par_id)
        throw new Exception();

    $res = sql_query("
        SELECT sent_id
        FROM sentences
        WHERE par_id = $par_id
        AND pos ".($next ? ">" : "<")." $sent_pos
        ORDER BY pos ".($next ? "ASC" : "DESC")."
        LIMIT 1
    ");

    if (sql_num_rows($res) == 1) {
        $r = sql_fetch_array($res);
        return $r['sent_id'];
    }

    // next/previous paragraph
    $r = sql_fetch_array(sql_query("
        SELECT book_id, pos
        FROM paragraphs
        WHERE par_id = $par_id LIMIT 1
    "));

    $book_id = $r['book_id'];
    $par_pos = $r['pos'];

    if (!$book_id)
        throw new Exception();

    $res = sql_query("
        SELECT sent_id
        FROM sentences s
        JOIN paragraphs p
            USING (par_id)
        JOIN books b
            USING (book_id)
        WHERE book_id = $book_id
        AND p.pos ".($next ? ">" : "<")." $par_pos
        ORDER BY p.pos ".($next ? "ASC" : "DESC").", s.pos ".($next ? "ASC" : "DESC")."
        LIMIT 1
    ");

    if (sql_num_rows($res) == 1) {
        $r = sql_fetch_array($res);
        return $r['sent_id'];
    }

    return 0;
}
function prepare_parse_indices($flag_array) {
    // note: $flag_array is 1-based, return values are 0-based
    $ret = array();
    foreach ($flag_array as $i => $val) {
        if ($val)
            $ret[] = $i-1;
    }
    return $ret;
}
function sentence_save($sent_id) {
    if (!$sent_id)
        throw new UnexpectedValueException();
    $flag = $_POST['var_flag'];  //what morphovariants are checked as possible (array of arrays)
    $dict = $_POST['dict_flag']; //whether this token has been reloaded from the dictionary (array)

    $res = sql_query("
        SELECT tf_id, tf_text, rev_text
        FROM tokens
        LEFT JOIN tf_revisions
            USING (tf_id)
        WHERE sent_id=$sent_id
        AND is_last = 1
        ORDER BY `pos`
    ");
    while ($r = sql_fetch_array($res)) {
        $tokens[$r['tf_id']] = array($r['tf_text'], $r['rev_text']);
    }
    $matches = array();
    $all_changes = array();
    if (count($flag) != count($tokens))
        throw new Exception();

    sql_begin();
    foreach ($tokens as $tf_id=>$v) {
        list($tf_text, $base_xml) = $v;
        //substitute the last revision's xml for one from dictionary if relevant
        if (isset($dict[$tf_id]) && $dict[$tf_id] == 1) {
            $parse = new MorphParseSet(false, $tf_text, false, true);
        } else {
            $parse = new MorphParseSet($base_xml);
        }

        if (sizeof($parse->parses) == 0)
            throw new Exception();
        // flags quantity check
        if (sizeof($parse->parses) != sizeof($flag[$tf_id]))
            throw new Exception();

        // XXX this is bad since order of parse selection from db
        //    is not guaranteed to be consistent
        $parse->filter_by_parse_index(prepare_parse_indices($flag[$tf_id]));

        $new_xml = $parse->to_xml();

        if ($base_xml != $new_xml) {
            //something's changed
            array_push($all_changes, array($tf_id, $new_xml));
        }
    }
    if (count($all_changes) > 0) {
        $revset_id = create_revset($_POST['comment']);
        foreach ($all_changes as $v)
            create_tf_revision($revset_id, $v[0], $v[1]);
    }
    sql_query("UPDATE sentences SET check_status='1' WHERE sent_id=$sent_id LIMIT 1");
    sql_commit();
}
function sentence_save_source($sent_id, $text) {
    sql_pe("UPDATE sentences SET source = ? WHERE sent_id=? LIMIT 1", array(trim($text), $sent_id));
}
function create_tf_revision($revset_id, $token_id, $rev_xml) {
    $res = sql_pe("SELECT rev_text FROM tf_revisions WHERE tf_id=? ORDER BY rev_id DESC LIMIT 1", array($token_id));
    if (sizeof($res) > 0 && $res[0]['rev_text'] === $rev_xml)
        // revisions are identical, do nothing
        return true;
    sql_begin();
    sql_pe("UPDATE tf_revisions SET is_last=0 WHERE tf_id=?", array($token_id));
    sql_pe("INSERT INTO `tf_revisions` VALUES(NULL, ?, ?, ?, 1)", array($revset_id, $token_id, $rev_xml));
    sql_commit();
}
function get_context_for_word($tf_id, $delta, $dir=0, $include_self=1, &$prepared_queries=NULL) {
    // dir stands for direction (-1 => left, 1 => right, 0 => both)
    // delta <= 0 stands for infinity
    $t = array();
    $tw = 0;
    $left_c = -1;  //if there is left context to be added
    $right_c = 0;  //same for right context
    $mw_pos = 0;
    
    // prepare the 1st query
    if ($prepared_queries === NULL)
        $prepared_queries = array(sql_prepare("
            SELECT MAX(tokens.pos) AS maxpos, MIN(tokens.pos) AS minpos, sent_id, source, book_id
            FROM tokens
                JOIN sentences USING (sent_id)
                JOIN paragraphs USING (par_id)
            WHERE sent_id = (
                SELECT sent_id
                FROM tokens
                WHERE tf_id=? LIMIT 1
            )
        "));

    sql_execute($prepared_queries[0], array($tf_id));
    $res = sql_fetchall($prepared_queries[0]);
    $r = $res[0];
    $sent_id = $r['sent_id'];
    $sentence_text = $r['source'];
    $book_id = $r['book_id'];
    $maxpos = $r['maxpos'];
    $minpos = $r['minpos'];

    // prepare the 2nd query
    // this is really bad unreadable code, sorry
    if (sizeof($prepared_queries) == 1) {
        $q = "SELECT tf_id, tf_text, pos FROM tokens WHERE sent_id = ?";
        if ($dir != 0 || $delta > 0) {
            $q_left = $dir <= 0 ? ($delta > 0 ? "(SELECT IF(pos > $delta, pos - $delta, 0) FROM tokens WHERE tf_id=? LIMIT 1)" : "0") : "(SELECT pos FROM tokens WHERE tf_id=? LIMIT 1)";
            $q_right = $dir >= 0 ? ($delta > 0 ? "(SELECT pos+$delta FROM tokens WHERE tf_id=? LIMIT 1)" : "1000") : "(SELECT pos FROM tokens WHERE tf_id=? LIMIT 1)";
            $q .= " AND pos BETWEEN $q_left AND $q_right";
        }

        $q .= " ORDER BY pos";
        $prepared_queries[] = sql_prepare($q);
    }

    // how many values should we provide?
    $bound = array($tf_id, $tf_id);
    if ($delta <= 0) {
        if ($dir == 0)
            $bound = array();
        else
            $bound = array($tf_id);
    }

    sql_execute($prepared_queries[1], array_merge(array($sent_id), $bound));

    foreach (sql_fetchall($prepared_queries[1]) as $r) {
        if ($delta > 0) {
            if ($left_c == -1) {
                $left_c = ($r['pos'] == $minpos) ? 0 : $r['tf_id'];
            }
            if ($mw_pos) {
                if ($r['pos'] > $mw_pos)
                    $right_c = $r['tf_id'];
                if ($right_c && $r['pos'] == $maxpos)
                    $right_c = 0;
            }
        }

        if ($include_self || $r['tf_id'] != $tf_id)
            $t[] = $r['tf_text'];
        if ($include_self && $r['tf_id'] == $tf_id) {
            $tw = sizeof($t) - 1;
            $mw_pos = $r['pos'];
        }
    }
    return array(
        'context' => $t,
        'mainword' => $tw,
        'has_left_context' => $left_c,
        'has_right_context' => $right_c,
        'sentence_id' => $sent_id,
        'sentence_text' => $sentence_text,
        'book_id' => $book_id
    );
}
function get_search_results($query, $exact_form=true) {
    $forms = array($query);
    if (!$exact_form) {
        include_once('lib_dict.php');
        $forms = get_all_forms_by_lemma_text($query);
    }
    $r = sql_fetch_array(sql_query("
        SELECT COUNT(*)
        FROM form2tf
        WHERE form_text IN (".join(",", array_map('sql_quote', $forms)).")
    "));

    $out = array('total' => $r[0], 'results' => array());
    $res = sql_query("
        SELECT tf_id
        FROM form2tf
        WHERE form_text IN (".join(",", array_map('sql_quote', $forms)).")
        LIMIT 100
    ");
    while ($r = sql_fetch_array($res))
        $out['results'][] = get_context_for_word($r['tf_id'], 20);
    return $out;
}
?>
