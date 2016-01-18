<?php
require_once('lib_annot.php');
require_once('lib_history.php');
require_once('lib_xml.php');
require_once('lib_morph_pools.php');

// GENERAL
function get_dict_stats() {
    $out = array();
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_g FROM `gram`"));
    $out['cnt_g'] = $r['cnt_g'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_l FROM `dict_lemmata` WHERE deleted=0"));
    $out['cnt_l'] = $r['cnt_l'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_f FROM `form2lemma`"));
    $out['cnt_f'] = $r['cnt_f'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_r FROM `dict_revisions` WHERE f2l_check=0"));
    $out['cnt_r'] = $r['cnt_r'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_v FROM `dict_revisions` WHERE dict_check=0"));
    $out['cnt_v'] = $r['cnt_v'];
    return $out;
}
function get_dict_search_results($get) {
    $out = array();
    $find_pos = sql_prepare("SELECT SUBSTR(grammems, 7, 4) AS gr FROM form2lemma WHERE lemma_id = ? LIMIT 1");
    if (isset($get['search_lemma'])) {
        $res = sql_pe("SELECT lemma_id, deleted FROM `dict_lemmata` WHERE `lemma_text`= ?", array($get['search_lemma']));
        $count = sizeof($res);
        $out['lemma']['count'] = $count;
        if ($count == 0)
            return $out;
        foreach ($res as $r) {
            sql_execute($find_pos, array($r['lemma_id']));
            $r1 = sql_fetch_array($find_pos);
            $out['lemma']['found'][] = array(
                'id' => $r['lemma_id'],
                'text' => $get['search_lemma'],
                'pos' => $r1['gr'],
                'is_deleted' => $r['deleted']
            );
        }
    }
    elseif (isset($get['search_form'])) {
        $res = sql_pe("SELECT DISTINCT dl.lemma_id, dl.lemma_text FROM `form2lemma` fl LEFT JOIN `dict_lemmata` dl ON (fl.lemma_id=dl.lemma_id) WHERE fl.`form_text`= ?", array($get['search_form']));
        $count = sizeof($res);
        $out['form']['count'] = $count;
        if ($count == 0)
            return $out;
        foreach ($res as $r) {
            sql_execute($find_pos, array($r['lemma_id']));
            $r1 = sql_fetch_array($find_pos);
            $out['form']['found'][] = array('id' => $r['lemma_id'], 'text' => $r['lemma_text'], 'pos' => $r1['gr']);
        }
    }
    return $out;
}
function get_all_forms_by_lemma_id($lid) {
    $res = sql_pe("SELECT rev_text FROM dict_revisions WHERE lemma_id=? AND is_last=1 LIMIT 1", array($lid));
    $parsed = parse_dict_rev($res[0]['rev_text']);
    $forms = array();
    foreach ($parsed['forms'] as $form)
        $forms[] = $form['text'];
    return array_unique($forms);
}
function get_all_forms_by_lemma_text($lemma) {
    $lemmata = get_dict_search_results(array('search_lemma' => $lemma));
    $forms = array($lemma);
    foreach ($lemmata['lemma']['found'] as $l)
        $forms = array_merge($forms, get_all_forms_by_lemma_id($l['id']));
    return array_unique($forms);
}
function dict_get_grammemes_by_order() {
    $res = sql_query("SELECT inner_id FROM gram ORDER BY orderby");
    $out = array();
    while ($r = sql_fetch_array($res))
        $out[] = $r['inner_id'];
    return $out;
}
function sort_grammemes(&$gram_array, $gram_order) {
    usort($gram_array, function($a, $b) use ($gram_order) {
        return array_search($a, $gram_order) < array_search($b, $gram_order) ? -1 : 1;
    });
}
function prepare_gram_array($gram_string, $gram_order) {
    $grams = array_filter(array_map("trim", explode(',', $gram_string)), "strlen");
    sort_grammemes($grams, $gram_order);
    return $grams;
}
function dict_get_select_gram() {
    $res = sql_query("SELECT `gram_id`, `inner_id` FROM `gram` ORDER by `inner_id`");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[$r['gram_id']] = $r['inner_id'];
    }
    return $out;
}
function get_link_types() {
    $res = sql_query("SELECT * FROM dict_links_types ORDER BY link_name");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[$r['link_id']] = $r['link_name'];
    }
    return $out;
}
function parse_dict_rev($text) {
    // output has the following structure:
    // lemma => array (text => lemma_text, grm => array (grm1, grm2, ...)),
    // forms => array (
    //     [0] => array (text => form_text, grm => array (grm1, grm2, ...)),
    //     [1] => ...
    // )
    $arr = xml2ary($text);
    $arr = $arr['dr']['_c'];
    $parsed = array();
    $parsed['lemma']['text'] = $arr['l']['_a']['t'];
    //the rest of the function should be refactored
    $t = array();
    foreach ($arr['l']['_c']['g'] as $garr) {
        if (isset($garr['v'])) {
            //if there is only one grammem
            $t[] = $garr['v'];
            break;
        }
        $t[] = $garr['_a']['v'];
    }
    $parsed['lemma']['grm'] = $t;
    if (isset($arr['f']['_a'])) {
        //if there is only one form
        $parsed['forms'][0]['text'] = $arr['f']['_a']['t'];
        $t = array();
        if (isset($arr['f']['_c'])) {
            //if there are grammems at all
            foreach ($arr['f']['_c']['g'] as $garr) {
                if (isset($garr['v'])) {
                    //if there is only one grammem
                    $t[] = $garr['v'];
                    break;
                }
                $t[] = $garr['_a']['v'];
            }
        }
        $parsed['forms'][0]['grm'] = $t;
    } else {
        foreach ($arr['f'] as $k=>$farr) {
            $parsed['forms'][$k]['text'] = $farr['_a']['t'];
            $t = array();
            foreach ($farr['_c']['g'] as $garr) {
                if (isset($garr['v'])) {
                    //if there is only one grammem
                    $t[] = $garr['v'];
                    break;
                }
                $t[] = $garr['_a']['v'];
            }
            $parsed['forms'][$k]['grm'] = $t;
        }
    }
    return $parsed;
}
function get_word_paradigm($lemma) {
    $res = sql_pe("SELECT rev_text FROM dict_revisions LEFT JOIN dict_lemmata USING (lemma_id) WHERE deleted=0 AND lemma_text=? AND is_last=1 LIMIT 1", array($lemma));
    if (sizeof($res) == 0)
        return false;
    $r = $res[0];
    $arr = parse_dict_rev($r['rev_text']);
    $out = array(
        'lemma_gram' => $arr['lemma']['grm'],
        'forms' => array()
    );

    $pseudo_stem = $arr['lemma']['text'];
    foreach ($arr['forms'] as $form) {
        $pseudo_stem = get_common_prefix($form['text'], $pseudo_stem);
    }

    $out['lemma_suffix_len'] = mb_strlen($arr['lemma']['text'], 'UTF-8') - mb_strlen($pseudo_stem, 'UTF-8');

    foreach ($arr['forms'] as $form) {
        $suffix_len = mb_strlen($form['text'], 'UTF-8') - mb_strlen($pseudo_stem, 'UTF-8');
        $out['forms'][] = array(
            'suffix' => $suffix_len ? mb_substr($form['text'], -$suffix_len, $suffix_len, 'UTF-8') : '',
            'grm' => $form['grm']
        );
    }

    return $out;
}
function get_common_prefix($word1, $word2) {
    if ($word1 == $word2)
        return $word1;
    $len1 = mb_strlen($word1, 'UTF-8');
    $len2 = mb_strlen($word2, 'UTF-8');
    $prefix = '';

    for ($i = 0; $i < min($len1, $len2); ++$i) {
        $char1 = mb_substr($word1, $i, 1, 'UTF-8');
        $char2 = mb_substr($word2, $i, 1, 'UTF-8');
        if ($char1 == $char2)
            $prefix .= $char1;
        else
            break;
    }
    return $prefix;
}
function form_exists($f) {
    $f = mb_strtolower($f, 'UTF-8');
    if (!preg_match('/^[а-яё]/u', $f)) {
        return -1;
    }
    $res = sql_pe("SELECT lemma_id FROM form2lemma WHERE form_text=? LIMIT 1", array($f));
    return sizeof($res);
}
function get_pending_updates($skip=0, $limit=500) {
    check_permission(PERM_DICT);
    $out = array('revisions' => array(), 'header' => array());

    $r = sql_fetch_array(sql_query("SELECT COUNT(*) cnt FROM updated_tokens"));
    $out['cnt_tokens'] = $r['cnt'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) cnt FROM updated_forms"));
    $out['cnt_forms'] = $r['cnt'];
    $res = sql_query("SELECT rev_id FROM dict_revisions WHERE f2l_check=0 LIMIT 1");
    $out['outdated_f2l'] = sql_num_rows($res);

    // header
    $res = sql_query("
        SELECT dict_revision, lemma_id, lemma_text, COUNT(token_id) AS cnt
        FROM updated_tokens ut
        LEFT JOIN dict_revisions dr ON (ut.dict_revision = dr.rev_id)
        LEFT JOIN dict_lemmata dl USING (lemma_id)
        GROUP BY dict_revision
        ORDER BY dict_revision
    ");
    $sum = 0;  // to count pages
    while ($r = sql_fetch_array($res)) {
        $out['header'][] = array(
            'lemma' => $r['lemma_text'],
            'lemma_id' => $r['lemma_id'],
            'revision' => $r['dict_revision'],
            'count' => $r['cnt'],
            'skip' => $sum
        );
        $sum += $r['cnt'];
    }

    // main table
    $res = sql_pe("
        SELECT token_id, tf_text, sent_id, dict_revision, lemma_id, dr.set_id,
            tfr.rev_text AS token_rev_text
        FROM updated_tokens ut
        LEFT JOIN dict_revisions dr ON (ut.dict_revision = dr.rev_id)
        LEFT JOIN tokens tf ON (ut.token_id = tf.tf_id)
        LEFT JOIN tf_revisions tfr USING (tf_id)
        WHERE tfr.is_last = 1
        ORDER BY dict_revision, token_id
        LIMIT ?, ?
    ", array($skip, $limit));

    $out['pages'] = array(
        'active' => $limit ? floor($skip / $limit) : 0,
        'total' => $limit ? ($out['cnt_tokens'] / $limit) : 1
    );

    $t = array();
    $last = NULL;
    foreach ($res as $r) {
        if ($last && $last['dict_revision'] != $r['dict_revision']) {
            $out['revisions'][] = array(
                'tokens' => $t,
                'id' => $last['dict_revision'],
                'diff' => dict_diff($last['lemma_id'], $last['set_id'])
            );
            $t = array();
        }

        $context = get_context_for_word($r['token_id'], 4);
        $context['context'][$context['mainword']] = '<b>'.htmlspecialchars($context['context'][$context['mainword']]).'</b>';
        $t[] = array(
            'id' => $r['token_id'],
            'text' => $r['tf_text'],
            'sentence_id' => $r['sent_id'],
            'context' => join(' ', $context['context']),
            'is_unkn' => preg_match('/v="UNKN"/', $r['token_rev_text']),
            'human_edits' => check_for_human_edits($r['token_id'])
        );
        $last = $r;
    }

    if (sizeof($t))
        $out['revisions'][] = array(
            'tokens' => $t,
            'id' => $last['dict_revision'],
            'diff' => dict_diff($last['lemma_id'], $last['set_id'])
        );

    return $out;
}
function check_for_human_edits($token_id) {
    $res = sql_pe("
        SELECT rev_id
        FROM tf_revisions
        LEFT JOIN rev_sets USING (set_id)
        WHERE tf_id = ?
        AND ((user_id > 0 AND comment != 'Update tokens from dictionary')
        OR (user_id = 0 AND comment LIKE '%annotation pool #%'))
        LIMIT 2
    ", array($token_id));
    return sizeof($res) > 1;
}
function check_safe_token_update($token_id, $rev_id) {
    // forbid updating if form2lemma is outdated
    $res = sql_query("SELECT rev_id FROM dict_revisions WHERE f2l_check=0 LIMIT 1");
    if (sql_num_rows($res) > 0)
        return false;

    // forbid updating if revision of the CURRENT TOKEN'S FORM is not latest
    $res = sql_pe("
        SELECT *
        FROM updated_tokens
        WHERE token_id = ?
        AND dict_revision > ?
        LIMIT 1
    ", array($token_id, $rev_id));
    return sizeof($res) == 0;
}
function forget_pending_token($token_id, $rev_id) {
    check_permission(PERM_DICT);
    sql_pe("DELETE FROM updated_tokens WHERE token_id=? AND dict_revision=?", array($token_id, $rev_id));
}
function update_pending_tokens($rev_id, $smart=false) {
    check_permission(PERM_DICT);
    $res = sql_pe("SELECT token_id FROM updated_tokens WHERE dict_revision=?", array($rev_id));
    sql_begin();
    $revset_id = create_revset("Update tokens from dictionary");
    foreach ($res as $r)
        update_pending_token($r['token_id'], $rev_id, $revset_id, $smart);
    sql_commit();
}
function smart_update_pending_token($parse_set, $rev_id) {
    // currently works only for
    // - deleted lemma
    // - lemma text change
    // - lemma gramset change

    $res = sql_pe("SELECT lemma_id, rev_text FROM dict_revisions WHERE rev_id=? LIMIT 1", array($rev_id));
    if (!sizeof($res))
        throw new Exception();
    $rev_text = $res[0]['rev_text'];
    $lemma_id = $res[0]['lemma_id'];

    if (!$rev_text) {
        // the revision deletes a lemma
        $parse_set->filter_by_lemma($lemma_id, false);
        return;
    }

    // get previous revision
    $res = sql_pe("SELECT rev_text FROM dict_revisions WHERE lemma_id=? ORDER BY rev_id DESC LIMIT 1,1", array($lemma_id));
    if (!sizeof($res))
        throw new Exception();
    $prev_rev_text = $res[0]['rev_text'];

    $prev_rev_parsed = parse_dict_rev($prev_rev_text);
    $new_rev_parsed = parse_dict_rev($rev_text);

    // cannot work if smth changed in the paradigm, not lemma
    $prev_forms = $prev_rev_parsed['forms'];
    $new_forms = $new_rev_parsed['forms'];

    if (sizeof($prev_forms) != sizeof($new_forms))
        throw new Exception("Smart mode unavailable");
    foreach ($prev_forms as $i => $pf) {
        if (
            $pf['text'] != $new_forms[$i]['text']
            || $pf['grm'] != $new_forms[$i]['grm']
        )
            throw new Exception("Smart mode unavailable");
    }

    $parse_set->set_lemma_text($lemma_id, $new_rev_parsed['lemma']['text']);
    $parse_set->replace_gram_subset($lemma_id, $prev_rev_parsed['lemma']['grm'], $new_rev_parsed['lemma']['grm']);
}
function update_pending_token($token_id, $rev_id, $revset_id=0, $smart=false) {
    check_permission(PERM_DICT);
    if (!check_safe_token_update($token_id, $rev_id))
        throw new Exception("Update forbidden");

    // ok, now we can safely update
    $res = sql_pe("SELECT tf_text FROM tokens WHERE tf_id=? LIMIT 1", array($token_id));
    $token_text = $res[0]['tf_text'];
    $res = sql_pe("SELECT rev_text FROM tf_revisions WHERE tf_id=? AND is_last=1 LIMIT 1", array($token_id));
    $previous_rev = $res[0]['rev_text'];

    if ($smart) {
        $parse = new MorphParseSet($previous_rev);
        smart_update_pending_token($parse, $rev_id);
    }
    else
        $parse = new MorphParseSet(false, $token_text);
    $new_rev = $parse->to_xml();
    // do nothing if nothing changed
    if ($previous_rev == $new_rev) {
        forget_pending_token($token_id, $rev_id);
        return true;
    }

    sql_begin();
    if (!$revset_id)
        $revset_id = create_revset("Update tokens from dictionary");
    create_tf_revision($revset_id, $token_id, $new_rev);
    forget_pending_token($token_id, $rev_id);
    delete_samples_by_token_id($token_id);

    sql_commit();
}
function get_top_absent_words() {
    $out = array();
    $res = sql_query("
        SELECT LOWER(tf_text) AS word, COUNT(tf_id) AS cnt
        FROM tokens
        LEFT JOIN tf_revisions USING (tf_id)
        WHERE is_last = 1
            AND LENGTH(tf_text) > 2
            AND rev_text LIKE '%\"UNKN\"%'
            AND tf_text NOT REGEXP '[0-9]'
        GROUP BY LOWER(tf_text)
        ORDER BY COUNT(tf_id) DESC
        LIMIT 500
    ");
    while ($r = sql_fetch_array($res))
        $out[] = array('word' => $r['word'], 'count' => $r['cnt']);

    return $out;
}

// DICTIONARY EDITOR
function get_lemma_editor($id) {
    $out = array('lemma' => array('id' => $id), 'errata' => array());
    if ($id == -1) return $out;
    $res = sql_pe("
        SELECT l.`lemma_text`, l.deleted, d.`rev_id`, d.`rev_text`
        FROM `dict_lemmata` l
            LEFT JOIN `dict_revisions` d
            USING (lemma_id)
        WHERE l.`lemma_id`=?
        AND d.is_last=1
        LIMIT 1
    ", array($id));
    $arr = parse_dict_rev($res[0]['rev_text']);
    $out['deleted'] = $res[0]['deleted'];
    $out['lemma']['text'] = $res[0]['lemma_text'];
    $out['lemma']['grms'] = implode(', ', $arr['lemma']['grm']);
    $out['lemma']['grms_raw'] = $arr['lemma']['grm'];
    foreach ($arr['forms'] as $farr) {
        $out['forms'][] = array('text' => $farr['text'], 'grms' => implode(', ', $farr['grm']), 'grms_raw' => $farr['grm']);
    }
    //links
    $res = sql_pe("
    (SELECT lemma1_id lemma_id, lemma_text, link_name, l.link_id, 1 AS target
        FROM dict_links l
        LEFT JOIN dict_links_types t ON (l.link_type=t.link_id)
        LEFT JOIN dict_lemmata lm ON (l.lemma1_id=lm.lemma_id)
        WHERE lemma2_id=?)
    UNION
    (SELECT lemma2_id lemma_id, lemma_text, link_name, l.link_id, 0 AS target
        FROM dict_links l
        LEFT JOIN dict_links_types t ON (l.link_type=t.link_id)
        LEFT JOIN dict_lemmata lm ON (l.lemma2_id=lm.lemma_id)
        WHERE lemma1_id=?)
    ", array($id, $id));
    foreach ($res as $r) {
        $out['links'][] = array('lemma_id' => $r['lemma_id'], 'lemma_text' => $r['lemma_text'], 'name' => $r['link_name'], 'id' => $r['link_id'], 'is_target' => (bool)$r['target']);
    }
    //errata
    $res = sql_pe("SELECT e.*, x.item_id, x.timestamp exc_time, x.comment exc_comment, u.user_shown_name AS user_name
        FROM dict_errata e
        LEFT JOIN dict_errata_exceptions x ON (e.error_type=x.error_type AND e.error_descr=x.error_descr)
        LEFT JOIN users u ON (x.author_id = u.user_id)
        WHERE e.rev_id =
        (SELECT rev_id FROM dict_revisions WHERE lemma_id=? AND is_last=1 LIMIT 1)
    ", array($id));
    foreach ($res as $r) {
        $out['errata'][] = array(
            'id' => $r['error_id'],
            'type' => $r['error_type'],
            'descr' => $r['error_descr'],
            'is_ok' => ($r['item_id'] > 0 ? 1 : 0),
            'author_name' => $r['user_name'],
            'exc_time' => $r['exc_time'],
            'comment' => $r['exc_comment']
        );
    }
    return $out;
}
function dict_add_lemma($array) {
    $ltext = $array['form_text'];
    $lgram = $array['form_gram'];
    $gram_order = dict_get_grammemes_by_order();
    $lemma_gram_new = prepare_gram_array($array['lemma_gram'], $gram_order);
    $lemma_text = $array['lemma_text'];
    $new_paradigm = array();
    foreach ($ltext as $i=>$text) {
        $text = trim($text);
        if ($text == '') {
            //the form is to be deleted, so we do nothing
        } elseif (strpos($text, ' ') !== false) {
            throw new UnexpectedValueException();
        } else {
            //TODO: perhaps some data validity check?
            array_push($new_paradigm, array($text, prepare_gram_array($lgram[$i], $gram_order)));
        }
    }
    $upd_forms = array();
    foreach ($new_paradigm as $form) {
        $upd_forms[] = $form[0];
    }
    sql_begin();
    //new lemma in dict_lemmata
    sql_pe("INSERT INTO dict_lemmata VALUES(NULL, ?, 0)", array(mb_strtolower($lemma_text)));
    $lemma_id = sql_insert_id();
    //array -> xml
    $new_xml = make_dict_xml($lemma_text, $lemma_gram_new, $new_paradigm);
    $rev_id = new_dict_rev($lemma_id, $new_xml, 0, $array['comment']);

    enqueue_updated_forms($upd_forms, $rev_id);

    sql_commit();
    return $lemma_id;
}
function calculate_updated_forms($old_rev, $new_rev) {
    // presume that $old_rev and $new_rev come from parse_dict_rev()
    $upd_forms = array();

    $old_lemma_text = $old_rev['lemma']['text'];
    $old_lemma_gram = implode(', ', $old_rev['lemma']['grm']);
    $old_paradigm = array();
    foreach ($old_rev['forms'] as $form_arr) {
        array_push($old_paradigm, array($form_arr['text'], implode(', ', $form_arr['grm'])));
    }

    $new_lemma_text = $new_rev['lemma']['text'];
    $new_lemma_gram = implode(', ', $new_rev['lemma']['grm']);
    $new_paradigm = array();
    foreach ($new_rev['forms'] as $form_arr) {
        array_push($new_paradigm, array($form_arr['text'], implode(', ', $form_arr['grm'])));
    }
    //if lemma's grammems or lemma text have changed then all forms have changed
    if ($new_lemma_gram != $old_lemma_gram || $new_lemma_text != $old_lemma_text) {
        foreach ($old_paradigm as $farr) {
            array_push($upd_forms, $farr[0]);
        }
        foreach ($new_paradigm as $farr) {
            array_push($upd_forms, $farr[0]);
        }
    } else {
        $int = paradigm_diff($old_paradigm, $new_paradigm);
        //..and insert them into `updated_forms`
        foreach ($int as $int_form) {
            array_push($upd_forms, $int_form[0]);
        }
    }
    return $upd_forms;
}
function dict_save($array) {
    check_permission(PERM_DICT);
    //it may be a totally new lemma
    if ($array['lemma_id'] == -1) {
        return dict_add_lemma($array);
    }

    // lemma might have been deleted, it is not editable then
    $r = sql_fetch_array(sql_query("SELECT deleted FROM dict_lemmata WHERE lemma_id = ".$array['lemma_id']." LIMIT 1"));
    if ($r['deleted'])
        throw new Exception("This lemma is not editable");

    $lemma_text = trim($array['lemma_text']);
    if (!$lemma_text)
        throw new UnexpectedValueException();
    $ltext = $array['form_text'];
    $lgram = $array['form_gram'];
    $gram_order = dict_get_grammemes_by_order();
    $lemma_gram_new = prepare_gram_array($array['lemma_gram'], $gram_order);

    $r = sql_fetch_array(sql_query("SELECT rev_text FROM dict_revisions WHERE lemma_id=".$array['lemma_id']." AND is_last=1 LIMIT 1"));
    $old_rev_parsed = parse_dict_rev($old_xml = $r['rev_text']);
    $old_lemma_text = $old_rev_parsed['lemma']['text'];

    $new_paradigm = array();
    foreach ($ltext as $i=>$text) {
        $text = trim($text);
        if ($text == '') {
            //the form is to be deleted, so we do nothing
        } elseif (strpos($text, ' ') !== false) {
            throw new UnexpectedValueException();
        } else {
            //TODO: perhaps some data validity check?
            array_push($new_paradigm, array($text, prepare_gram_array($lgram[$i], $gram_order)));
        }
    }

    sql_begin();
    //array -> xml
    $new_xml = make_dict_xml($lemma_text, $lemma_gram_new, $new_paradigm);
    if ($lemma_text != $old_lemma_text || $new_xml != $old_xml) {
        //something's really changed
        if ($lemma_text != $old_lemma_text)
            sql_pe(
                "UPDATE dict_lemmata SET lemma_text=? WHERE lemma_id=?",
                array($lemma_text, $array['lemma_id'])
            );
        $rev_id = new_dict_rev($array['lemma_id'], $new_xml, 0, $array['comment']);
        enqueue_updated_forms(calculate_updated_forms($old_rev_parsed, parse_dict_rev($new_xml)), $rev_id);
        sql_commit();
    }
    return $array['lemma_id'];
}
function make_dict_xml($lemma_text, $lemma_gram, $paradigm) {
    $new_xml = '<dr><l t="'.htmlspecialchars(mb_strtolower($lemma_text)).'">';
    foreach ($lemma_gram as $gr) {
        $new_xml .= '<g v="'.htmlspecialchars($gr).'"/>';
    }
    $new_xml .= '</l>';
    //paradigm
    foreach ($paradigm as $new_form) {
        list($txt, $gram) = $new_form;
        $new_xml .= '<f t="'.htmlspecialchars(mb_strtolower($txt)).'">';
        foreach ($gram as $gr) {
            $new_xml .= '<g v="'.htmlspecialchars($gr).'"/>';
        }
        $new_xml .= '</f>';
    }
    $new_xml .= '</dr>';
    return $new_xml;
}
function enqueue_updated_forms($forms, $revision_id) {
    $ins = sql_prepare("INSERT INTO `updated_forms` VALUES (?, ?)");
    foreach (array_unique($forms) as $upd_form)
        sql_execute($ins, array($upd_form, $revision_id));
}
function new_dict_rev($lemma_id, $new_xml, $revset_id=0, $comment = '') {
    if (!$lemma_id || !$new_xml)
        throw new UnexpectedValueException();
    sql_begin();
    if (!$revset_id)
        $revset_id = create_revset($comment);
    sql_pe("UPDATE dict_revisions SET is_last=0 WHERE lemma_id=?", array($lemma_id));
    sql_pe("INSERT INTO `dict_revisions` VALUES(NULL, ?, ?, ?, 0, 0, 1)", array($revset_id, $lemma_id, $new_xml));
    $new_id = sql_insert_id();
    sql_commit();
    return $new_id;
}
function paradigm_diff($array1, $array2) {
    $diff = array();
    foreach ($array1 as $form_array) {
        if (!in_array($form_array, $array2))
            array_push($diff, $form_array);
    }
    foreach ($array2 as $form_array) {
        if (!in_array($form_array, $array1))
            array_push($diff, $form_array);
    }
    return $diff;
}
function del_lemma($id) {
    check_permission(PERM_DICT);
    //delete links (but preserve history)
    $res = sql_pe("SELECT link_id FROM dict_links WHERE lemma1_id=? OR lemma2_id=?", array($id, $id));
    sql_begin();
    $revset_id = create_revset("Delete lemma $id");
    foreach ($res as $r)
        del_link($r['link_id'], $revset_id);

    // create empty revision
    sql_pe("UPDATE dict_revisions SET is_last=0 WHERE lemma_id=?", array($id));
    sql_pe("INSERT INTO dict_revisions VALUES (NULL, ?, ?, '', 1, 1, 1)", array($revset_id, $id));
    $rev_id = sql_insert_id();

    //update `updated_forms`
    $res = sql_pe("SELECT rev_text FROM dict_revisions WHERE lemma_id=? ORDER BY `rev_id` DESC LIMIT 1, 1", array($id));
    $pdr = parse_dict_rev($res[0]['rev_text']);
    $updated_forms = array();
    foreach ($pdr['forms'] as $form)
        $updated_forms[] = $form['text'];
    enqueue_updated_forms($updated_forms, $rev_id);
    //delete forms from form2lemma
    sql_pe("DELETE FROM `form2lemma` WHERE lemma_id=?", array($id));
    //delete lemma
    sql_pe("UPDATE dict_lemmata SET deleted=1 WHERE lemma_id=? LIMIT 1", array($id));
    sql_commit();
}
function del_link($link_id, $revset_id=0) {
    check_permission(PERM_DICT);
    $res = sql_pe("SELECT * FROM dict_links WHERE link_id=? LIMIT 1", array($link_id));
    if (!sizeof($res))
        throw new UnexpectedValueException();
    sql_begin();
    if (!$revset_id) $revset_id = create_revset();
    sql_query("INSERT INTO dict_links_revisions VALUES(NULL, '$revset_id', '".$res[0]['lemma1_id']."', '".$res[0]['lemma2_id']."', '".$res[0]['link_type']."', '0')");
    sql_pe("DELETE FROM dict_links WHERE link_id=? LIMIT 1", array($link_id));
    sql_commit();
}
function add_link($from_id, $to_id, $link_type, $revset_id=0) {
    check_permission(PERM_DICT);
    if ($from_id <= 0 || $to_id <= 0 || !$link_type)
        throw new UnexpectedValueException();
    sql_begin();
    if (!$revset_id) $revset_id = create_revset();
    sql_pe("INSERT INTO dict_links VALUES(NULL, ?, ?, ?)", array($from_id, $to_id, $link_type));
    sql_pe("INSERT INTO dict_links_revisions VALUES(NULL, ?, ?, ?, ?, 1)", array($revset_id, $from_id, $to_id, $link_type));
    sql_commit();
}
function change_link_direction($link_id) {
    check_permission(PERM_DICT);
    if (!$link_id)
        throw new UnexpectedValueException();
    sql_begin();
    $revset_id = create_revset();
    $res = sql_pe("SELECT * FROM dict_links WHERE link_id=? LIMIT 1", array($link_id));
    del_link($link_id, $revset_id);
    add_link($res[0]['lemma2_id'], $res[0]['lemma1_id'], $res[0]['link_type'], $revset_id);
    sql_commit();
}

// GRAMMEM EDITOR
function get_grammem_editor($order) {
    $out = array();
    $orderby = $order == 'id' ? 'inner_id' :
        ($order == 'outer' ? 'outer_id' : 'orderby');
    $res = sql_query("SELECT g1.`gram_id`, g1.`parent_id`, g1.`inner_id`, g1.`outer_id`, g1.`gram_descr`, g1.`orderby`, g2.`inner_id` AS `parent_name` FROM `gram` g1 LEFT JOIN `gram` g2 ON (g1.parent_id=g2.gram_id) ORDER BY g1.`$orderby`");
    while ($r = sql_fetch_array($res)) {
        $class = strlen($r['inner_id']) != 4 ? 'gramed_bad' :
            (preg_match('/^[A-Z0-9-]+$/', $r['inner_id']) ? 'gramed_pos' :
            (preg_match('/[A-Z0-9][A-Z0-9][a-z0-9-][a-z0-9-]/', $r['inner_id']) ? 'gramed_group' :
            (preg_match('/[A-Z][a-z0-9-][a-z0-9-][a-z0-9-]/', $r['inner_id']) ? 'gramed_label' : '')));
        $out[] = array(
            'order' => $r['orderby'],
            'id' => $r['gram_id'],
            'name' => $r['inner_id'],
            'outer_id' => $r['outer_id'],
            'description' => htmlspecialchars($r['gram_descr']),
            'parent_name' => $r['parent_name'],
            'css_class' => $class
        );
    }
    return $out;
}
function add_grammem($inner_id, $group, $outer_id, $descr) {
    check_permission(PERM_DICT);
    if (!$inner_id)
        throw new UnexpectedValueException();
    $r = sql_fetch_array(sql_query("SELECT MAX(`orderby`) AS `m` FROM `gram`"));
    sql_pe("INSERT INTO `gram` VALUES(NULL, ?, ?, ?, ?, ?)", array($group, $inner_id, $outer_id, $descr, $r['m']+1));
}
function del_grammem($grm_id) {
    check_permission(PERM_DICT);
    sql_pe("DELETE FROM `gram` WHERE `gram_id`=? LIMIT 1", array($grm_id));
}
function edit_grammem($id, $inner_id, $outer_id, $descr) {
    check_permission(PERM_DICT);
    if (!$id || !$inner_id)
        throw new UnexpectedValueException();
    sql_pe(
        "UPDATE `gram` SET `inner_id`=?, `outer_id`=?, `gram_descr`=? WHERE `gram_id`=? LIMIT 1",
        array($inner_id, $outer_id, $descr, $id)
    );
}

//ERRATA
function get_dict_errata($all, $rand) {
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_v FROM `dict_revisions` WHERE dict_check=0"));
    $out = array('lag' => $r['cnt_v']);
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_t FROM `dict_errata`"));
    $out['total'] = $r['cnt_t'];
    $res = sql_query("SELECT e.*, r.lemma_id, r.set_id, x.item_id, x.timestamp exc_time, x.comment exc_comment, u.user_shown_name AS user_name
        FROM dict_errata e
        LEFT JOIN dict_errata_exceptions x ON (e.error_type=x.error_type AND e.error_descr=x.error_descr)
        LEFT JOIN users u ON (x.author_id = u.user_id)
        LEFT JOIN dict_revisions r ON (e.rev_id=r.rev_id)
        ORDER BY ".($rand?'RAND()':'error_id').($all?'':' LIMIT 200'));
    while ($r = sql_fetch_array($res)) {
        $out['errors'][] = array(
            'id' => $r['error_id'],
            'timestamp' => $r['timestamp'],
            'revision' => $r['rev_id'],
            'type' => $r['error_type'],
            'description' => preg_replace('/<([^>]+)>/', '<a href="?act=edit&amp;id='.$r['lemma_id'].'">$1</a>', $r['error_descr']),
            'lemma_id' => $r['lemma_id'],
            'set_id' => $r['set_id'],
            'is_ok' => ($r['item_id'] > 0 ? 1 : 0),
            'author_name' => $r['user_name'],
            'exc_time' => $r['exc_time'],
            'comment' => $r['exc_comment']
        );
    }
    return $out;
}
function clear_dict_errata($old) {
    check_permission(PERM_DICT);
    if ($old) {
        sql_query("UPDATE dict_revisions SET dict_check='0'");
        return true;
    }

    $res = sql_query("SELECT MAX(rev_id) AS m FROM dict_revisions GROUP BY lemma_id");
    sql_begin();
    while ($r = sql_fetch_array($res))
        sql_query("UPDATE dict_revisions SET dict_check='0' WHERE rev_id=".$r['m']." LIMIT 1");
    sql_commit();
}
function mark_dict_error_ok($id, $comment) {
    check_permission(PERM_DICT);
    if (!$id)
        throw new UnexpectedValueException();

    sql_pe("INSERT INTO dict_errata_exceptions VALUES(
        NULL,
        (SELECT error_type FROM dict_errata WHERE error_id=? LIMIT 1),
        (SELECT error_descr FROM dict_errata WHERE error_id=? LIMIT 1),
        ?,
        ?,
        ?
    )", array($id, $id, $_SESSION['user_id'], time(), $comment));
}
function get_gram_restrictions($hide_auto) {
    $res = sql_query("SELECT r.restr_id, r.obj_type, r.restr_type, r.auto, g1.inner_id `if`, g2.inner_id `then`
        FROM gram_restrictions r
            LEFT JOIN gram g1 ON (r.if_id=g1.gram_id)
            LEFT JOIN gram g2 ON (r.then_id=g2.gram_id)".
            ($hide_auto ? " WHERE `auto`=0" : "")
        ." ORDER BY r.restr_id");
    $out = array('gram_options' => '');
    while ($r = sql_fetch_array($res)) {
        $out['list'][] = array(
            'id' => $r['restr_id'],
            'if_id' => $r['if'],
            'then_id' => $r['then'],
            'type' => $r['restr_type'],
            'obj_type' => $r['obj_type'],
            'auto' => $r['auto']
        );
    }
    $res = sql_query("SELECT gram_id, inner_id FROM gram order by inner_id");
    while ($r = sql_fetch_array($res)) {
        $out['gram_options'][$r['gram_id']] = $r['inner_id'];
    }
    return $out;
}
function add_dict_restriction($post) {
    check_permission(PERM_DICT);
    sql_begin();
    sql_query("INSERT INTO gram_restrictions VALUES(NULL, '".(int)$post['if']."', '".(int)$post['then']."', '".(int)$post['rtype']."', '".((int)$post['if_type'] + (int)$post['then_type'])."', '0')");
    calculate_gram_restrictions();
    sql_commit();
}
function del_dict_restriction($id) {
    check_permission(PERM_DICT);
    sql_begin();
    sql_pe("DELETE FROM gram_restrictions WHERE restr_id=? LIMIT 1", array($id));
    calculate_gram_restrictions();
    sql_commit();
}
function calculate_gram_restrictions() {
    check_permission(PERM_DICT);
    sql_begin();
    sql_query("DELETE FROM gram_restrictions WHERE `auto`=1");

    $restr = array();
    $res = sql_query("SELECT r.if_id, r.then_id, r.obj_type, r.restr_type, g1.gram_id gram1, g2.gram_id gram2
        FROM gram_restrictions r
        LEFT JOIN gram g1 ON (r.then_id = g1.parent_id)
        LEFT JOIN gram g2 ON (g1.gram_id = g2.parent_id)
        WHERE r.restr_type>0");
    while ($r = sql_fetch_array($res)) {
        $restr[] = $r['if_id'].'#'.$r['then_id'].'#'.$r['obj_type'].'#'.$r['restr_type'];
        if ($r['gram1'])
            $restr[] = $r['if_id'].'#'.$r['gram1'].'#'.$r['obj_type'].'#'.$r['restr_type'];
        if ($r['gram2'])
            $restr[] = $r['if_id'].'#'.$r['gram2'].'#'.$r['obj_type'].'#'.$r['restr_type'];
    }
    $restr = array_unique($restr);
    foreach ($restr as $quad) {
        list($if, $then, $type, $w0) = explode('#', $quad);
        $w = ($w0 == 1 ? 0 : 2);
        if (sql_num_rows(sql_query("SELECT restr_id FROM gram_restrictions WHERE if_id=$if AND then_id=$then AND obj_type=$type AND restr_type=$w")) == 0)
            sql_query("INSERT INTO gram_restrictions VALUES(NULL, '$if', '$then', '$w', '$type', '1')");
    }
    sql_commit();
}
?>
