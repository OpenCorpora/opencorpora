<?php
require_once('lib_xml.php');
require_once('lib_books.php');

// GENERAL
function get_dict_stats() {
    $out = array();
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_g FROM `gram`"));
    $out['cnt_g'] = $r['cnt_g'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_l FROM `dict_lemmata`"));
    $out['cnt_l'] = $r['cnt_l'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_f FROM `form2lemma`"));
    $out['cnt_f'] = $r['cnt_f'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_r FROM `dict_revisions` WHERE f2l_check=0"));
    $out['cnt_r'] = $r['cnt_r'];
    return $out;
}
function get_dict_search_results($post) {
    $out = array();
    if (isset($post['search_lemma'])) {
        $q = mysql_real_escape_string($post['search_lemma']);
        $res = sql_query("SELECT lemma_id FROM `dict_lemmata` WHERE `lemma_text`='$q'");
        $count = sql_num_rows($res);
        $out['lemma']['count'] = $count;
        if ($count == 0)
            return $out;
        while ($r = sql_fetch_array($res)) {
            $out['lemma']['found'][] = array('id' => $r['lemma_id'], 'text' => $q);
        }
    }
    elseif (isset($post['search_form'])) {
        $q = mysql_real_escape_string($post['search_form']);
        $res = sql_query("SELECT DISTINCT dl.lemma_id, dl.lemma_text FROM `form2lemma` fl LEFT JOIN `dict_lemmata` dl ON (fl.lemma_id=dl.lemma_id) WHERE fl.`form_text`='$q'");
        $count = sql_num_rows($res);
        $out['form']['count'] = $count;
        if ($count == 0)
            return $out;
        while ($r = sql_fetch_array($res)) {
            $out['form']['found'][] = array('id' => $r['lemma_id'], 'text' => $r['lemma_text']);
        }
    }
    return $out;
}
function generate_tf_rev($token) {
    $out = '<tfr t="'.htmlspecialchars($token).'">';
    if (preg_match('/^[А-Яа-яЁё][А-Яа-яЁё\-\']*$/u', $token)) {
        $res = sql_query("SELECT lemma_id, lemma_text, grammems FROM form2lemma WHERE form_text='$token'");
        if (sql_num_rows($res) > 0) {
            while($r = sql_fetch_array($res)) {
                $out .= '<v><l id="'.$r['lemma_id'].'" t="'.$r['lemma_text'].'">'.$r['grammems'].'</l></v>';
            }
        } else {
            $out .= '<v><l id="0" t="'.htmlspecialchars(lc($token)).'"><g v="UNKN"/></l></v>';
        }
    } elseif (preg_match('/^[\,\.\:\;\-\(\)\'\"\[\]\?\!\/]+$/', $token)) {
        $out .= '<v><l id="0" t="'.htmlspecialchars($token).'"><g v="PNCT"/></l></v>';
    } else {
        $out .= '<v><l id="0" t="'.htmlspecialchars($token).'"><g v="UNKN"/></l></v>';
    }
    $out .= '</tfr>';
    return $out;
}
function dict_get_select_gram() {
    $res = sql_query("SELECT `gram_id`, `inner_id` FROM `gram` ORDER by `inner_id`");
    $out = '';
    while($r = sql_fetch_array($res)) {
        $out .= '<option value="'.$r['gram_id'].'">'.$r['inner_id'].'</option>';
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
        foreach($arr['f'] as $k=>$farr) {
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
function form_exists($f) {
    $f = lc($f);
    if (!preg_match('/[А-Яа-яЁё\-\']/u', $f)) {
        return -1;
    }
    return sql_num_rows(sql_query("SELECT lemma_id FROM form2lemma WHERE form_text='".mysql_real_escape_string($f)."' LIMIT 1"));
}

// DICTIONARY EDITOR
function get_lemma_editor($id) {
    $out = array('lemma' => array('id' => $id));
    if ($id == -1) return $out;
    $r = sql_fetch_array(sql_query("SELECT l.`lemma_text`, d.`rev_id`, d.`rev_text` FROM `dict_lemmata` l LEFT JOIN `dict_revisions` d ON (l.lemma_id = d.lemma_id) WHERE l.`lemma_id`=$id ORDER BY d.rev_id DESC LIMIT 1"));
    $arr = parse_dict_rev($r['rev_text']);
    $out['lemma']['text'] = $arr['lemma']['text'];
    $out['lemma']['grms'] = implode(', ', $arr['lemma']['grm']);
    foreach($arr['forms'] as $farr) {
        $out['forms'][] = array('text' => $farr['text'], 'grms' => implode(', ', $farr['grm']));
    }
    //links
    $res = sql_query("(SELECT lemma1_id lemma_id, lemma_text, link_name FROM dict_links l LEFT JOIN dict_links_types t ON (l.link_type=t.link_id) LEFT JOIN dict_lemmata lm ON (l.lemma1_id=lm.lemma_id) WHERE lemma2_id=$id) UNION (SELECT lemma2_id lemma_id, lemma_text, link_name FROM dict_links l LEFT JOIN dict_links_types t ON (l.link_type=t.link_id) LEFT JOIN dict_lemmata lm ON (l.lemma2_id=lm.lemma_id) WHERE lemma1_id=$id)", 1, 1);
    while($r = sql_fetch_array($res)) {
        $out['links'][] = array('lemma_id' => $r['lemma_id'], 'lemma_text' => $r['lemma_text'], 'name' => $r['link_name']);
    }
    return $out;
}
function dict_add_lemma($array) {
    $ltext = $array['form_text'];
    $lgram = $array['form_gram'];
    $lemma_gram_new = $array['lemma_gram'];
    $lemma_text = $array['lemma_text'];
    $new_paradigm = array();
    foreach($ltext as $i=>$text) {
        $text = trim($text);
        if ($text == '') {
            //the form is to be deleted, so we do nothing
        } elseif (strpos($text, ' ') !== false) {
            show_error("Error: a form cannot contain whitespace ($text)");
            return;
        } else {
            //TODO: perhaps some data validity check?
            array_push($new_paradigm, array($text, $lgram[$i]));
        }
    }
    $upd_forms = array();
    foreach($new_paradigm as $form) {
        $upd_forms[] = $form[0];
    }
    $upd_forms = array_unique($upd_forms);
    foreach($upd_forms as $upd_form) {
        if (!sql_query("INSERT INTO `updated_forms` VALUES('".mysql_real_escape_string($upd_form)."')")) {
            show_error("Error at updated_forms :(");
            exit;
        }
    }
    //new lemma in dict_lemmata
    if (!sql_query("INSERT INTO dict_lemmata VALUES(NULL, '".mysql_real_escape_string($lemma_text)."')")) {
        show_error();
        exit;
    }
    $lemma_id = sql_insert_id();
    //array -> xml
    $new_xml = make_dict_xml($lemma_text, $lemma_gram_new, $new_paradigm);
    $res = new_dict_rev($lemma_id, $new_xml);
    if ($res) {
        header("Location:dict.php?act=edit&saved&id=$lemma_id");
        return;
    } else show_error("Error on saving");
}
function dict_save($array) {
    //it may be a totally new lemma
    if ($array['lemma_id'] == -1) {
        dict_add_lemma($array);
        return;
    }
    $ltext = $array['form_text'];
    $lgram = $array['form_gram'];
    $lemma_gram_new = $array['lemma_gram'];
    //let's construct the old paradigm
    $r = sql_fetch_array(sql_query("SELECT rev_text FROM dict_revisions WHERE lemma_id=".$array['lemma_id']." ORDER BY `rev_id` DESC LIMIT 1"));
    $pdr = parse_dict_rev($old_xml = $r['rev_text']);
    $lemma_text = $pdr['lemma']['text'];
    $lemma_gram_old = implode(', ', $pdr['lemma']['grm']);
    $old_paradigm = array();
    foreach($pdr['forms'] as $form_arr) {
        array_push($old_paradigm, array($form_arr['text'], implode(', ', $form_arr['grm'])));
    }
    $new_paradigm = array();
    foreach($ltext as $i=>$text) {
        $text = trim($text);
        if ($text == '') {
            //the form is to be deleted, so we do nothing
        } elseif (strpos($text, ' ') !== false) {
            die ("Error: a form cannot contain whitespace ($text)");
        } else {
            //TODO: perhaps some data validity check?
            array_push($new_paradigm, array($text, $lgram[$i]));
        }
    }
    //calculate which forms are actually updated
    $upd_forms = array();
    //if lemma's grammems have changed then all forms have changed
    if ($lemma_gram_new != $lemma_gram_old) {
        foreach($old_paradigm as $farr) {
            array_push($upd_forms, $farr[0]);
        }
        foreach($new_paradigm as $farr) {
            array_push($upd_forms, $farr[0]);
        }
    } else {
        $int = paradigm_diff($old_paradigm, $new_paradigm);
        //..and insert them into `updated_forms`
        foreach($int as $int_form) {
            array_push($upd_forms, $int_form[0]);
        }
    }
    $upd_forms = array_unique($upd_forms);
    foreach($upd_forms as $upd_form) {
        if (!sql_query("INSERT INTO `updated_forms` VALUES('".mysql_real_escape_string($upd_form)."')")) {
            show_error("Error at updated_forms :(");
            exit;
        }
    }
    //array -> xml
    $new_xml = make_dict_xml($lemma_text, $lemma_gram_new, $new_paradigm);
    if ($new_xml != $old_xml) {
        //something's really changed
        $res = new_dict_rev($array['lemma_id'], $new_xml);
        if ($res) {
            header("Location:dict.php?act=edit&saved&id=".$array['lemma_id']);
            return;
        } else show_error("Error on saving");
    } else {
        header("Location:dict.php?act=edit&id=".$array['lemma_id']);
        return;
    }
}
function make_dict_xml($lemma_text, $lemma_gram, $paradigm) {
    $new_xml = '<dr><l t="'.htmlspecialchars($lemma_text).'">';
    //lemma's grammems
    $lg = explode(',', $lemma_gram);
    foreach($lg as $gr) {
        $new_xml .= '<g v="'.htmlspecialchars(trim($gr)).'"/>';
    }
    $new_xml .= '</l>';
    //paradigm
    foreach($paradigm as $new_form) {
        list($txt, $gram) = $new_form;
        $new_xml .= '<f t="'.htmlspecialchars($txt).'">';
        $gram = explode(',', $gram);
        foreach($gram as $gr) {
            $new_xml .= '<g v="'.htmlspecialchars(trim($gr)).'"/>';
        }
        $new_xml .= '</f>';
    }
    $new_xml .= '</dr>';
    return $new_xml;
}
function new_dict_rev($lemma_id, $new_xml) {
    if (!$lemma_id || !$new_xml) return 0;
    $revset_id = create_revset();
    if (!$revset_id) return 0;
    if (sql_query("INSERT INTO `dict_revisions` VALUES(NULL, '$revset_id', '$lemma_id', '".mysql_real_escape_string($new_xml)."', '0')")) {
        return 1;
    }
    return 0;
}
function paradigm_diff($array1, $array2) {
    $diff = array();
    foreach($array1 as $form_array) {
        if(!in_array($form_array, $array2))
            array_push($diff, $form_array);
    }
    foreach($array2 as $form_array) {
        if(!in_array($form_array, $array1))
            array_push($diff, $form_array);
    }
    return $diff;
}
function del_lemma($id) {
    error_reporting(0);
    //delete links (but preserve history)
    $res = sql_query("SELECT link_id FROM dict_links WHERE lemma1_id=$id OR lemma2_id=$id");
    $revset_id = create_revset();
    print "revset created ";
    while($r = sql_fetch_array($res)) {
        print "will delete link $r[link_id] ";
        if (!del_link($r['link_id'], $revset_id)) {
            show_error();
            return;
        }
    }
    //update `updated_forms`
    $r = sql_fetch_array(sql_query("SELECT rev_text FROM dict_revisions WHERE lemma_id=$id ORDER BY `rev_id` DESC LIMIT 1"));
    $pdr = parse_dict_rev($r['rev_text']);
    foreach($pdr['forms'] as $form) {
        print $form['text'];
        if (!sql_query("INSERT INTO `updated_forms` VALUES('".$form['text']."')")) {
            show_error();
            return;
        }
    }
    //delete forms from form2lemma
    sql_query("DELETE FROM `form2lemma` WHERE lemma_id=$id");
    //delete lemma
    if (sql_query("INSERT INTO dict_lemmata_deleted (SELECT * FROM dict_lemmata WHERE lemma_id=$id LIMIT 1)") && sql_query("DELETE FROM dict_lemmata WHERE lemma_id=$id LIMIT 1")) {
        header("Location:dict.php");
        return;
    } else
        show_error();
}
function del_link($link_id, $revset_id=0) {
    $r = sql_fetch_array(sql_query("SELECT * FROM dict_links WHERE link_id=$link_id LIMIT 1"));
    if (!$revset_id) $revset_id = create_revset();
    if (sql_query("INSERT INTO dict_links_revisions VALUES(NULL, '$revset_id', '".$r['lemma1_id']."', '".$r['lemma2_id']."', '".$r['link_type']."', '0')") &&
        sql_query("DELETE FROM dict_links WHERE link_id=$link_id LIMIT 1")) {
        return 1;
    }
    return 0;
}

// GRAMMEM EDITOR
function get_grammem_editor() {
    $out = array();
    $res = sql_query("SELECT g1.`gram_id`, g1.`parent_id`, g1.`inner_id`, g1.`outer_id`, g1.`gram_descr`, g2.`inner_id` AS `parent_name` FROM `gram` g1 LEFT JOIN `gram` g2 ON (g1.parent_id=g2.gram_id) ORDER BY g1.`orderby`");
    while($r = sql_fetch_array($res)) {
        $class = strlen($r['inner_id']) != 4 ? 'gramed_bad' :
            (preg_match('/^[A-Z0-9-]+$/', $r['inner_id']) ? 'gramed_pos' :
            (preg_match('/[A-Z0-9-][A-Z0-9-][a-z0-9-][a-z0-9-]/', $r['inner_id']) ? 'gramed_group' :
            (preg_match('/[A-Z0-9-][a-z0-9-][a-z0-9-][a-z0-9-]/', $r['inner_id']) ? 'gramed_label' : '')));
        $out[] = array(
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
    $r = sql_fetch_array(sql_query("SELECT MAX(`orderby`) AS `m` FROM `gram`"));
    if (sql_query("INSERT INTO `gram` VALUES(NULL, '$group', '$inner_id', '$outer_id', '$descr', '".($r['m']+1)."')")) {
        header("Location:dict.php?act=gram");
        return;
    } else {
        show_error();
    }
}
function del_grammem($grm_id) {
    if (sql_query("DELETE FROM `gram` WHERE `gram_id`=$grm_id LIMIT 1")) {
        header("Location:dict.php?act=gram");
        return;
    } else
        show_error();
}
function move_grammem($grm_id, $dir) {
    $r = sql_fetch_array(sql_query("SELECT `orderby` as `ord` FROM `gram` WHERE gram_id=$grm_id"));
    $ord = $r['ord'];
    if ($dir == 'up') {
        $q = sql_query("SELECT MAX(`orderby`) as `ord` FROM `gram` WHERE `orderby`<$ord");
        if ($q) {
            $r = sql_fetch_array($q);
            $ord2 = $r['ord'];
        }
    } else {
        $q = sql_query("SELECT MIN(`orderby`) as `ord` FROM `gram` WHERE `orderby`>$ord");
        if ($q) {
            $r = sql_fetch_array($q);
            $ord2 = $r['ord'];
        }
    }
    if (!isset($ord2)) {
        header('Location:dict.php?act=gram');
        return;
    }
    if (sql_query("UPDATE `gram` SET `orderby`='$ord' WHERE `orderby`=$ord2 LIMIT 1") &&
        sql_query("UPDATE `gram` SET `orderby`='$ord2' WHERE `gram_id`=$grm_id LIMIT 1")) {
        header('Location:dict.php?act=gram');
        return;
    } else {
        show_error();
    }
}
function edit_grammem($id, $inner_id, $outer_id, $descr) {
    if (sql_query("UPDATE `gram` SET `inner_id`='$inner_id', `outer_id`='$outer_id', `gram_descr`='$descr' WHERE `gram_id`=$id LIMIT 1")) {
        header('Location:dict.php?act=gram');
        return;
    } else {
        show_error();
    }
}

// ADDING TEXTS
function split2paragraphs($txt) {
    return preg_split('/\r?\n\r?\n\r?/', $txt);
}
function split2sentences($txt) {
    return preg_split('/[\r\n]+/', $txt);
}
function tokenize($txt) {
    $words = explode(' ', $txt);
    $txt = array();
    $token = '';
    $last_letter = 0;
    foreach ($words as $word) {
        #whole word check
        if (form_exists($word)) {
            $txt[] = $word;
            $last_letter = 1;
            continue;
        }
        #hyphen check
        if (preg_match('/([А-Яа-яЁё]+)-([А-Яа-яЁё]+)/u', $word, $matches)) {
            $txt[] = $matches[1];
            $txt[] = '-';
            $txt[] = $matches[2];
            $last_letter = 1;
            continue;
        }
        for($i = 0; $i < mb_strlen($word, 'utf-8'); ++$i) {
            $char = mb_substr($word, $i, 1, 'utf-8');
            if (preg_match('/[\.,!\?;:\(\)\[\]\/\xAB\xBB]/u', $char)) {
                if ($token) {
                    $txt[] = $token;
                    $token = '';
                }
                if ($last_letter)
                    $txt[] = $char;
                else
                    $txt[sizeof($txt)-1] .= $char;
                $last_letter = 0;
            } else {
                $token .= $char;
                $last_letter = 1;
            }
        }
        if ($token)
            $txt[] = $token;
        $token = '';
    }
    return $txt;
}
function addtext_check($txt) {
    $out = array('full' => $txt, 'select' => books_get_select(0));
    $pars = split2paragraphs($txt);
    foreach ($pars as $par) {
        $par_array = array();
        $sents = split2sentences($par);
        foreach ($sents as $sent) {
            $sent_array = array();
            $tokens = tokenize($sent);
            foreach ($tokens as $token) {
                $sent_array['tokens'][] = array('text' => $token, 'class' => form_exists($token));
            }
            $par_array['sentences'][] = $sent_array;
        }
        $out['paragraphs'][] = $par_array;
    }
    return $out;
}
function addtext_add($text, $book_id, $par_num) {
    if (!$text || !$book_id || !$par_num) return 0;
    $revset_id = create_revset();
    if (!$revset_id) return 0;
    $pars = split2paragraphs($text);
    foreach($pars as $par) {
        //adding a paragraph
        if (!sql_query("INSERT INTO `paragraphs` VALUES(NULL, '$book_id', '".($par_num++)."')")) return 0;
        $par_id = sql_insert_id();
        $sent_num = 1;
        $sents = split2sentences($par);
        foreach($sents as $sent) {
            //adding a sentence
            if (!sql_query("INSERT INTO `sentences` VALUES(NULL, '$par_id', '".($sent_num++)."', '0')")) return 0;
            $sent_id = sql_insert_id();
            $token_num = 1;
            //strip excess whitespace
            $sent = preg_replace('/\s\s+/', ' ', $sent);
            $tokens = tokenize($sent);
            foreach ($tokens as $token) {
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
