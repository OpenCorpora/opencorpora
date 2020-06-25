<?php
require_once('constants.php');
require_once('lib_annot.php');
require_once('lib_history.php');
require_once('lib_xml.php');
require_once('lib_morph_pools.php');
require_once('Lexeme.php');

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
function get_dict_search_results($search_lemma, $search_form=false) {
    $out = array();
    $find_pos = sql_prepare("SELECT SUBSTR(grammems, 7, 4) AS gr FROM form2lemma WHERE lemma_id = ? LIMIT 1");
    if ($search_lemma) {
        $res = sql_pe("SELECT lemma_id, deleted FROM `dict_lemmata` WHERE `lemma_text`= ?", array($search_lemma));
        $count = sizeof($res);
        $out['lemma']['count'] = $count;
        if ($count == 0)
            return $out;
        foreach ($res as $r) {
            sql_execute($find_pos, array($r['lemma_id']));
            $r1 = sql_fetch_array($find_pos);
            $out['lemma']['found'][] = array(
                'id' => $r['lemma_id'],
                'text' => $search_lemma,
                'pos' => $r1['gr'],
                'is_deleted' => $r['deleted']
            );
        }
    }
    elseif ($search_form) {
        $res = sql_pe("SELECT DISTINCT dl.lemma_id, dl.lemma_text FROM `form2lemma` fl LEFT JOIN `dict_lemmata` dl ON (fl.lemma_id=dl.lemma_id) WHERE fl.`form_text`= ?", array($search_form));
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
    $lex = new Lexeme($res[0]['rev_text']);
    return array_unique($lex->get_all_forms_texts());
}
function get_all_forms_by_lemma_text($lemma) {
    $lemmata = get_dict_search_results($lemma);
    $forms = array($lemma);
    foreach ($lemmata['lemma']['found'] as $l)
        $forms = array_merge($forms, get_all_forms_by_lemma_id($l['id']));
    return array_unique($forms);
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
function get_link_type_info($type_id) {
    $res = sql_pe("SELECT COUNT(*) AS cnt FROM dict_links WHERE link_type = ?", array($type_id));
    $data = array('total' => $res[0]['cnt'], 'samples' => array());
    $res = sql_pe("SELECT link_name FROM dict_links_types WHERE link_id = ? LIMIT 1", array($type_id));
    $data['name'] = $res[0]['link_name'];
    $res = sql_pe("
        SELECT lemma1_id, lemma2_id, lm1.lemma_text AS lemma1_text, lm2.lemma_text AS lemma2_text
        FROM dict_links lk
        LEFT JOIN dict_lemmata lm1
            ON (lk.lemma1_id = lm1.lemma_id)
        LEFT JOIN dict_lemmata lm2
            ON (lk.lemma2_id = lm2.lemma_id)
        WHERE link_type = ?
        LIMIT 100
    ", array($type_id));
    foreach ($res as $row) {
        $data['samples'][] = array(
            'lemma1' => [$row['lemma1_id'], $row['lemma1_text']],
            'lemma2' => [$row['lemma2_id'], $row['lemma2_text']]
        );
    }
    return $data;
}
function get_word_paradigm($lemma) {
    $res = sql_pe("SELECT rev_text FROM dict_revisions LEFT JOIN dict_lemmata USING (lemma_id) WHERE deleted=0 AND lemma_text=? AND is_last=1 LIMIT 1", array($lemma));
    if (sizeof($res) == 0)
        return false;
    $r = $res[0];
    $lex = new Lexeme($r['rev_text']);
    $out = array(
        'lemma_gram' => $lex->lemma->grammemes,
        'forms' => array()
    );

    $pseudo_stem = $lex->lemma->text;
    foreach ($lex->get_all_forms_texts() as $form) {
        $pseudo_stem = get_common_prefix($form, $pseudo_stem);
    }

    $out['lemma_suffix_len'] = mb_strlen($lex->lemma->text) - mb_strlen($pseudo_stem);

    foreach ($lex->forms as $form) {
        $suffix_len = mb_strlen($form->text) - mb_strlen($pseudo_stem);
        $out['forms'][] = array(
            'suffix' => $suffix_len ? mb_substr($form->text, -$suffix_len, $suffix_len) : '',
            'grm' => $form->grammemes
        );
    }

    return $out;
}
function get_common_prefix($word1, $word2) {
    if ($word1 == $word2)
        return $word1;
    $len1 = mb_strlen($word1);
    $len2 = mb_strlen($word2);
    $prefix = '';

    for ($i = 0; $i < min($len1, $len2); ++$i) {
        $char1 = mb_substr($word1, $i, 1);
        $char2 = mb_substr($word2, $i, 1);
        if ($char1 == $char2)
            $prefix .= $char1;
        else
            break;
    }
    return $prefix;
}
function form_exists($f) {
    $f = mb_strtolower($f);
    if (!preg_match('/^[а-яё]/u', $f)) {
        return -1;
    }
    $res = sql_pe("SELECT lemma_id FROM form2lemma WHERE form_text=? LIMIT 1", array($f));
    return sizeof($res);
}
function get_pending_updates($skip=0, $limit=500) {
    check_permission(PERM_DISAMB);
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
function forget_pending_tokens($rev_id) {
    check_permission(PERM_DISAMB);
    sql_pe("DELETE FROM updated_tokens WHERE dict_revision=?", array($rev_id));
}
function forget_pending_token($token_id, $rev_id) {
    check_permission(PERM_DISAMB);
    sql_pe("DELETE FROM updated_tokens WHERE token_id=? AND dict_revision=?", array($token_id, $rev_id));
}
function update_pending_tokens($rev_id, $smart=false) {
    check_permission(PERM_DISAMB);
    $res = sql_pe("SELECT token_id FROM updated_tokens WHERE dict_revision=?", array($rev_id));
    sql_begin();
    current_revset("Update tokens from dictionary"); // create revset
    foreach ($res as $r)
        update_pending_token($r['token_id'], $rev_id, $smart);
    sql_commit();
}
function can_smart_update($deleted_forms, $added_forms_texts) {
    return sizeof($deleted_forms) == 0 || sizeof($added_forms_texts) == 0;
}
function discard_yo($form) {
    return array('text' => strtr($form->text, array('ё' => 'е')), 'grm' => $form->grammemes);
}
function discard_yo_all($forms) {
    $ret = array();
    foreach ($forms as $form) {
        $ret[] = discard_yo($form);
    }
    return $ret;
}
function get_added_and_deleted_forms($prev_forms, $new_forms) {
    $deleted_forms = array();
    foreach ($prev_forms as $pf) {
        if (!in_array(discard_yo($pf), discard_yo_all($new_forms)))
            $deleted_forms[] = $pf;
    }
    $added_forms_texts = array();
    foreach ($new_forms as $nf) {
        if (!in_array(discard_yo($nf), discard_yo_all($prev_forms))) {
            $added_forms_texts[] = $nf->text;
        }
    }
    return array($added_forms_texts, $deleted_forms);
}
function smart_update_pending_token(MorphParseSet $parse_set, $rev_id) {
    // currently works only for
    // - deleted lemma
    // - added lemma (only this lemma's forms are added)
    // - lemma text change (with optional form e/yo changes)
    // - lemma gramset change
    // - added form(s): adds all forms of this lemma homonymous to the added one(s)
    // - deleted form(s): deletes all forms of this lemma HOMONYMOUS to the deleted one(s)
    
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
    $res = sql_pe("SELECT rev_text FROM dict_revisions WHERE lemma_id=? AND rev_id<? ORDER BY rev_id DESC LIMIT 1", array($lemma_id, $rev_id));
    if (!sizeof($res)) {
        // the revision adds a lemma
        $new_parses = new MorphParseSet(false, $parse_set->token_text, false, false, $lemma_id);
        $parse_set->merge_from($new_parses);
        return;
    }
    $prev_rev_text = $res[0]['rev_text'];

    $lex_prev = new Lexeme($prev_rev_text);
    $lex_new = new Lexeme($rev_text);

    // cannot work if smth changed in the paradigm, not lemma
    // unless the change is addition/deletion of new forms and/or pure text changes

    list($added_forms_texts, $deleted_forms) = get_added_and_deleted_forms($lex_prev->forms, $lex_new->forms);

    if (!can_smart_update($deleted_forms, $added_forms_texts)) {
        throw new Exception("Smart mode unavailable");
    }

    foreach (array_unique($added_forms_texts) as $ftext) {
        $new_parses = new MorphParseSet(false, $ftext, false, false, $lemma_id);
        $parse_set->merge_from($new_parses);
    }
    foreach ($deleted_forms as $df) {
        $parse_set->remove_parse($lemma_id, array_merge($lex_prev->lemma->grammemes, $df->grammemes));
    }

    // process lemma changes
    $parse_set->set_lemma_text($lemma_id, $lex_new->lemma->text);
    $parse_set->replace_gram_subset($lemma_id, $lex_prev->lemma->grammemes, $lex_new->lemma->grammemes);
}
function update_pending_token($token_id, $rev_id, $smart=false) {
    check_permission(PERM_DISAMB);
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
    $revset_id = current_revset("Update tokens from dictionary");
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
    $lex = new Lexeme($res[0]['rev_text']);
    $out['deleted'] = $res[0]['deleted'];
    $out['lemma']['text'] = $res[0]['lemma_text'];
    $out['lemma']['grms'] = implode(', ', $lex->lemma->grammemes);
    $out['lemma']['grms_raw'] = $lex->lemma->grammemes;
    foreach ($lex->forms as $form) {
        $out['forms'][] = array('text' => $form->text, 'grms' => implode(', ', $form->grammemes), 'grms_raw' => $form->grammemes);
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
function calculate_updated_forms(Lexeme $old_lex, Lexeme $new_lex) {
    $upd_forms = array();

    $old_lemma_text = $old_lex->lemma->text;
    $old_lemma_gram = implode(', ', $old_lex->lemma->grammemes);
    $old_paradigm = array();
    foreach ($old_lex->forms as $form) {
        array_push($old_paradigm, array($form->text, implode(', ', $form->grammemes)));
    }

    $new_lemma_text = $new_lex->lemma->text;
    $new_lemma_gram = implode(', ', $new_lex->lemma->grammemes);
    $new_paradigm = array();
    foreach ($new_lex->forms as $form) {
        array_push($new_paradigm, array($form->text, implode(', ', $form->grammemes)));
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
function dict_save($lemma_id, $lemma_text, $lemma_gram, $form_text, $form_gram, $comment) {
    $do_save = user_has_permission(PERM_DICT);
    $rev_id = 0;
    $updated_forms = array();
    if (!$lemma_text)
        throw new UnexpectedValueException();

    $lex = new Lexeme;
    $lex->set_lemma($lemma_text, $lemma_gram);
    $lex->set_paradigm($form_text, $form_gram);
    $new_xml = $lex->to_xml();

    sql_begin();
    //it may be a totally new lemma
    if ($lemma_id == -1) {
        if ($do_save) {
            sql_pe("INSERT INTO dict_lemmata VALUES(NULL, ?, 0)", array(mb_strtolower($lemma_text)));
            $lemma_id = sql_insert_id();
        } else {
            $lemma_id = 0;
        }
        $rev_id = new_dict_rev($lemma_id, $new_xml, $comment, !$do_save);
        $updated_forms = $lex->get_all_forms_texts();
    } else {
        // lemma might have been deleted, it is not editable then
        $r = sql_fetch_array(sql_query("SELECT deleted FROM dict_lemmata WHERE lemma_id = $lemma_id LIMIT 1"));
        if ($r['deleted'])
            throw new Exception("This lemma is not editable");

        $r = sql_fetch_array(sql_query("SELECT rev_text FROM dict_revisions WHERE lemma_id=$lemma_id AND is_last=1 LIMIT 1"));
        $old_lex = new Lexeme($old_xml = $r['rev_text']);
        $old_lemma_text = $old_lex->lemma->text;

        if ($lemma_text == $old_lemma_text && $new_xml == $old_xml) {
            // nothing changed
            return $lemma_id;
        }
        if ($do_save && $lemma_text != $old_lemma_text) {
            sql_pe(
                "UPDATE dict_lemmata SET lemma_text=? WHERE lemma_id=?",
                array($lemma_text, $lemma_id)
            );
        }
        $rev_id = new_dict_rev($lemma_id, $new_xml, $comment, !$do_save);
        $updated_forms = calculate_updated_forms($old_lex, new Lexeme($new_xml));
    }
    if ($do_save && sizeof($updated_forms) > 0) {
        enqueue_updated_forms($updated_forms, $rev_id);
    }
    sql_commit();
    return $lemma_id;
}
function enqueue_updated_forms($forms, $revision_id) {
    $ins = sql_prepare("INSERT INTO `updated_forms` VALUES (?, ?)");
    foreach (array_unique($forms) as $upd_form)
        sql_execute($ins, array($upd_form, $revision_id));
}
function new_dict_rev($lemma_id, $new_xml, $comment = '', $pending = false) {
    if (!$new_xml)
        throw new UnexpectedValueException();
    sql_begin();
    $new_id = -1;

    if ($pending) {
        sql_pe(
            "INSERT INTO dict_revisions_ugc SET user_id = ?, lemma_id = ?, rev_text = ?, comment = ?",
            array($_SESSION['user_id'], $lemma_id, $new_xml, $comment)
        );
    } else {
        if (!$lemma_id)
            throw new UnexpectedValueException();
        $revset_id = current_revset($comment);
        sql_pe("UPDATE dict_revisions SET is_last=0 WHERE lemma_id=?", array($lemma_id));
        sql_pe("INSERT INTO `dict_revisions` VALUES(NULL, ?, ?, ?, 0, 0, 1, 0)", array($revset_id, $lemma_id, $new_xml));
        $new_id = sql_insert_id();
    }

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
    $revset_id = current_revset("Delete lemma $id");
    foreach ($res as $r)
        del_link($r['link_id']);

    // create empty revision
    sql_pe("UPDATE dict_revisions SET is_last=0 WHERE lemma_id=?", array($id));
    sql_pe("INSERT INTO dict_revisions VALUES (NULL, ?, ?, '', 1, 1, 1, 0)", array($revset_id, $id));
    $rev_id = sql_insert_id();

    //update `updated_forms`
    $res = sql_pe("SELECT rev_text FROM dict_revisions WHERE lemma_id=? ORDER BY `rev_id` DESC LIMIT 1, 1", array($id));
    $lex = new Lexeme($res[0]['rev_text']);
    enqueue_updated_forms($lex->get_all_forms_texts(), $rev_id);
    //delete forms from form2lemma
    sql_pe("DELETE FROM `form2lemma` WHERE lemma_id=?", array($id));
    //delete lemma
    sql_pe("UPDATE dict_lemmata SET deleted=1 WHERE lemma_id=? LIMIT 1", array($id));
    sql_commit();
}
function get_pending_dict_edits() {
    $res = sql_fetchall(sql_query("
        SELECT ugc.rev_id, ugc.user_id, u.user_shown_name AS user_name, created_ts, lemma_id, ugc.rev_text AS rev_text_new, dr.rev_text AS rev_text_old, comment
        FROM dict_revisions_ugc AS ugc
        LEFT JOIN dict_revisions dr
            USING (lemma_id)
        LEFT JOIN users u
            USING (user_id)
        WHERE ugc.status = 0
            AND (dr.is_last = 1 OR lemma_id = 0)
        ORDER BY ugc.rev_id
    "));
    foreach ($res as &$r) {
        $r['diff'] = php_diff(format_xml($r['rev_text_old']), format_xml($r['rev_text_new']));
    }
    return $res;
}
function dict_approve_edit($rev_id) {
    check_permission(PERM_DICT);
    $res = sql_pe("
        SELECT lemma_id, rev_text, status, comment
        FROM dict_revisions_ugc
        WHERE rev_id = ?
        LIMIT 1
    ", array($rev_id));
    if (!sizeof($res) || $res[0]['status'] != DICT_UGC_PENDING)
        throw new Exception();
    $row = $res[0];
    sql_begin();
    $lemma_id = $row['lemma_id'];
    $lex = new Lexeme($row['rev_text']);
    $updated_forms = [];
    if ($lemma_id == 0) {
        sql_pe("INSERT INTO dict_lemmata VALUES(NULL, ?, 0)", array(mb_strtolower($lex->lemma->text)));
        $lemma_id = sql_insert_id();
        $updated_forms = $lex->get_all_forms_texts();
    } else {
        $r = sql_fetch_array(sql_query("SELECT rev_text FROM dict_revisions WHERE lemma_id=$lemma_id AND is_last=1 LIMIT 1"));
        $old_lex = new Lexeme($r['rev_text']);
        $updated_forms = calculate_updated_forms($old_lex, $lex);
    }
    $new_rev_id = new_dict_rev($lemma_id, $row['rev_text'], "Merge edit #$rev_id", false);
    if (sizeof($updated_forms) > 0) {
        enqueue_updated_forms($updated_forms, $new_rev_id);
    }
    sql_pe("UPDATE dict_revisions_ugc SET status = ".DICT_UGC_APPROVED.", moder_id = ? WHERE rev_id = ? LIMIT 1", array($_SESSION['user_id'], $rev_id));
    sql_pe("UPDATE dict_revisions SET ugc_rev_id = ? WHERE rev_id = ? LIMIT 1", array($rev_id, $new_rev_id));
    sql_commit();
}
function dict_reject_edit($rev_id) {
    check_permission(PERM_DICT);
    $res = sql_pe("
        SELECT status
        FROM dict_revisions_ugc
        WHERE rev_id = ?
        LIMIT 1
    ", array($rev_id));
    if (!sizeof($res) || $res[0]['status'] != DICT_UGC_PENDING)
        throw new Exception();
    sql_pe("UPDATE dict_revisions_ugc SET status = ".DICT_UGC_REJECTED.", moder_id = ? WHERE rev_id = ? LIMIT 1", array($_SESSION['user_id'], $rev_id));
}
function del_link($link_id) {
    check_permission(PERM_DICT);
    $res = sql_pe("SELECT * FROM dict_links WHERE link_id=? LIMIT 1", array($link_id));
    if (!sizeof($res))
        throw new UnexpectedValueException();
    sql_begin();
    $revset_id = current_revset();
    sql_query("INSERT INTO dict_links_revisions VALUES(NULL, '$revset_id', '".$res[0]['lemma1_id']."', '".$res[0]['lemma2_id']."', '".$res[0]['link_type']."', '0')");
    sql_pe("DELETE FROM dict_links WHERE link_id=? LIMIT 1", array($link_id));
    sql_commit();
}
function add_link($from_id, $to_id, $link_type) {
    check_permission(PERM_DICT);
    if ($from_id <= 0 || $to_id <= 0 || !$link_type)
        throw new UnexpectedValueException();
    sql_begin();
    $revset_id = current_revset();
    sql_pe("INSERT INTO dict_links VALUES(NULL, ?, ?, ?)", array($from_id, $to_id, $link_type));
    sql_pe("INSERT INTO dict_links_revisions VALUES(NULL, ?, ?, ?, ?, 1)", array($revset_id, $from_id, $to_id, $link_type));
    sql_commit();
}
function change_link_direction($link_id) {
    check_permission(PERM_DICT);
    if (!$link_id)
        throw new UnexpectedValueException();
    sql_begin();
    $res = sql_pe("SELECT * FROM dict_links WHERE link_id=? LIMIT 1", array($link_id));
    del_link($link_id);
    add_link($res[0]['lemma2_id'], $res[0]['lemma1_id'], $res[0]['link_type']);
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
    $q = "UPDATE dict_revisions SET dict_check=0";
    if (!$old) {
        $q .= " WHERE is_last=1";
    }
    sql_query($q);
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
function add_dict_restriction($if, $then, $rtype, $if_type, $then_type) {
    check_permission(PERM_DICT);
    sql_begin();
    sql_pe("
        INSERT INTO gram_restrictions VALUES(NULL, ?, ?, ?, ?, 0)
    ", array($if, $then, $rtype, $if_type + $then_type));
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
