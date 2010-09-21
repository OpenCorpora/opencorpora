<?php
require_once('lib_xml.php');
require_once('lib_books.php');

// GENERAL
function get_dict_stats() {
    $out = array();
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_gt FROM `gram_types`"));
    $out['cnt_gt'] = $r['cnt_gt'];
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
    $out = '<tf_rev text="'.htmlspecialchars($token).'">';
    if (preg_match('/[А-Яа-яЁё\-]/u', $token) && $token != '-') {
        $res = sql_query("SELECT lemma_id, lemma_text, grammems FROM form2lemma WHERE form_text='$token'");
        if (sql_num_rows($res) > 0) {
            while($r = sql_fetch_array($res)) {
                $out .= '<var><lemma id="'.$r['lemma_id'].'" text="'.$r['lemma_text'].'">'.$r['grammems'].'</lemma></var>';
            }
        } else {
            $out .= '<var><lemma id="0" text="'.htmlspecialchars(lc($token)).'"><grm val="UnknownPOS"/></lemma></var>';
        }
    } elseif (preg_match('/^[\,\.\:\;\-\(\)\'\"\[\]\?\!\/]+$/', $token)) {
        $out .= '<var><lemma id="0" text="'.htmlspecialchars($token).'"><grm val="PM"/></lemma></var>';
    } else {
        $out .= '<var><lemma id="0" text="'.htmlspecialchars($token).'"><grm val="UnknownPOS"/></lemma></var>';
    }
    $out .= '</tf_rev>';
    return $out;
}
function dict_get_select_gramtype() {
    $res = sql_query("SELECT `type_id`, `type_name` FROM `gram_types` ORDER by `type_name`");
    $out = '';
    while($r = sql_fetch_array($res)) {
        $out .= '<option value="'.$r['type_id'].'">'.$r['type_name'].'</option>';
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
        foreach ($arr['f']['_c']['g'] as $garr) {
            if (isset($garr['v'])) {
                //if there is only one grammem
                $t[] = $garr['v'];
                break;
            }
            $t[] = $garr['_a']['v'];
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
    if (!preg_match('/[А-Яа-я\-\']/u', $f)) {
        return -1;
    }
    return sql_num_rows(sql_query("SELECT lemma_id FROM form2lemma WHERE form_text='".mysql_real_escape_string($f)."' LIMIT 1"));
}

// DICTIONARY EDITOR
function get_lemma_editor($id) {
    $out = array('lemma' => array('id' => $id));
    $r = sql_fetch_array(sql_query("SELECT l.`lemma_text`, d.`rev_id`, d.`rev_text` FROM `dict_lemmata` l LEFT JOIN `dict_revisions` d ON (l.lemma_id = d.lemma_id) WHERE l.`lemma_id`=$id ORDER BY d.rev_id DESC LIMIT 1"));
    $arr = parse_dict_rev($r['rev_text']);
    $out['lemma']['text'] = $arr['lemma']['text'];
    $out['lemma']['grms'] = implode(', ', $arr['lemma']['grm']);
    foreach($arr['forms'] as $farr) {
        $out['forms'][] = array('text' => $farr['text'], 'grms' => implode(', ', $farr['grm']));
    }
    return $out;
}
function dict_save($array) {
    //print_r($array);
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
            die("Error at updated_forms :(");
        }
    }
    //array -> xml
    $new_xml = make_dict_xml($lemma_text, $lemma_gram_new, $new_paradigm);
    if ($new_xml != $old_xml) {
        //something's really changed
        $res = new_dict_rev($array['lemma_id'], $new_xml);
        if ($res) {
            header("Location:dict.php?act=edit&saved&id=".$array['lemma_id']);
        } else die("Error on saving");
    } else {
        header("Location:dict.php?act=edit&id=".$array['lemma_id']);
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

// GRAMMEM EDITOR
function get_grammem_editor() {
    $out = array('select' => dict_get_select_gramtype());
    $res = sql_query("SELECT gt.*, g.* FROM `gram_types` gt LEFT JOIN `gram` g ON (gt.type_id = g.gram_type) ORDER BY gt.`orderby`, g.`orderby`");
    while($r = sql_fetch_array($res)) {
        $out['groups'][$r['type_id']]['name'] = $r['type_name'];
        if ($r['gram_id'])
            $out['groups'][$r['type_id']]['grammems'][] = array('id' => $r['gram_id'], 'name' => $r['inner_id'], 'aot_id' => $r['outer_id'], 'description' => $r['gram_descr']);
    }
    return $out;
}
function add_gramtype($name) {
    $r = sql_fetch_array(sql_query("SELECT MAX(`orderby`) AS `m` FROM `gram_types`"));
    if (sql_query("INSERT INTO `gram_types` VALUES(NULL, '$name', '".($r['m']+1)."')")) {
        header("Location:dict.php?act=gram");
    } else {
        show_error();
    }
}
function move_gramtype($group_id, $dir) {
    $r = sql_fetch_array(sql_query("SELECT `orderby` as `ord` FROM `gram_types` WHERE type_id=$group_id"));
    $ord = $r['ord'];
    if ($dir == 'up') {
        $q = sql_query("SELECT MAX(`orderby`) as `ord` FROM `gram_types` WHERE `orderby`<$ord");
        if ($q) {
            $r = sql_fetch_array($q);
            $ord2 = $r['ord'];
        }
    } else {
        $q = sql_query("SELECT MIN(`orderby`) as `ord` FROM `gram_types` WHERE `orderby`>$ord");
        if ($q) {
            $r = sql_fetch_array($q);
            $ord2 = $r['ord'];
        }
    }
    if (!isset($ord2))
        header('Location:dict.php?act=gram');
    if (sql_query("UPDATE `gram_types` SET `orderby`='$ord' WHERE `orderby`=$ord2 LIMIT 1") &&
        sql_query("UPDATE `gram_types` SET `orderby`='$ord2' WHERE `type_id`=$group_id LIMIT 1")) {
        header('Location:dict.php?act=gram');
    } else {
        show_error();
    }
}
function del_gramtype($group_id) {
    if (sql_query("DELETE FROM `gram` WHERE gram_type=$group_id") &&
        sql_query("DELETE FROM gram_types WHERE type_id=$group_id LIMIT 1"))
        header('Location:dict.php?act=gram');
    else
        show_error();
}
function add_grammem($inner_id, $group, $outer_id, $descr) {
    $r = sql_fetch_array(sql_query("SELECT MAX(`orderby`) AS `m` FROM `gram` WHERE `gram_type`=$group"));
    if (sql_query("INSERT INTO `gram` VALUES(NULL, '$group', '$inner_id', '$outer_id', '$descr', '".($r['m']+1)."')")) {
        header("Location:dict.php?act=gram");
    } else {
        show_error();
    }
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
    if (!isset($ord2))
        header('Location:dict.php?act=gram');
    if (sql_query("UPDATE `gram` SET `orderby`='$ord' WHERE `orderby`=$ord2 LIMIT 1") &&
        sql_query("UPDATE `gram` SET `orderby`='$ord2' WHERE `gram_id`=$grm_id LIMIT 1")) {
        header('Location:dict.php?act=gram');
    } else {
        show_error();
    }
}
function edit_grammem($id, $inner_id, $outer_id, $descr) {
    if (sql_query("UPDATE `gram` SET `inner_id`='$inner_id', `outer_id`='$outer_id', `gram_descr`='$descr' WHERE `gram_id`=$id LIMIT 1")) {
        header('Location:dict.php?act=gram');
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
function addtext_check($txt) {
    $out = array('full' => $txt, 'select' => books_get_select(0));
    $pars = split2paragraphs($txt);
    foreach ($pars as $par) {
        $par_array = array();
        $sents = split2sentences($par);
        foreach ($sents as $sent) {
            $sent_array = array();
            $tokens = explode(' ', $sent);
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
            $tokens = explode(' ', $sent);
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
