<?php
function get_sentence($sent_id) {
    $r = sql_fetch_array(sql_query("SELECT `check_status`, source FROM sentences WHERE sent_id=$sent_id LIMIT 1"));
    $out = array(
        'id' => $sent_id,
        'status' => $r['check_status'],
        'source' => $r['source']
    );
    //counting comments
    $r = sql_fetch_array(sql_query("SELECT COUNT(comment_id) comm_cnt FROM sentence_comments WHERE sent_id=$sent_id"));
    $out['comment_count'] = $r['comm_cnt'];
    //looking for source name
    $r = sql_fetch_array(sql_query("
        SELECT book_id
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
    $res = sql_query("SELECT tf_id, tf_text FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
    $j = 0; //token position, for further highlighting
    $gram_descr = array();  //associative array to keep info about grammemes
    while ($r = sql_fetch_array($res)) {
        array_push ($tf_text, '<span id="src_token_'.($j++).'">'.$r['tf_text'].'</span>');
        $rev = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=".$r['tf_id']." AND is_last=1 LIMIT 1"));
        $arr = xml2ary($rev['rev_text']);

        $out['tokens'][] = array(
            'tf_id'        => $r['tf_id'],
            'tf_text'      => $r['tf_text'],
            'variants'     => get_morph_vars($arr['tfr']['_c']['v'], $gram_descr)
        );
    }
    $out['fulltext'] = typo_spaces(implode(' ', $tf_text), 1);
    return $out;
}
function get_morph_vars($xml_arr, &$gram_descr) {
    if (isset($xml_arr['_c']) && is_array($xml_arr['_c'])) {
        //the only variant
        $var = get_morph_vars_inner($xml_arr, 1);
        $t = array();
        foreach ($var['gram_list'] as $gr) {
            if (!isset($gram_descr[$gr['inner']])) {
                $r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='".$gr['inner']."' COLLATE utf8_bin LIMIT 1"));
                $gram_descr[$gr['inner']] = array($r[0], $r[1]);
            }
            $t[] = array('inner' => $gr['inner'], 'outer' => $gram_descr[$gr['inner']][0], 'descr' => $gram_descr[$gr['inner']][1]);
        }
        $var['gram_list'] = $t;
        return array($var);
    } else {
        //multiple variants
        $out = array();
        $i = 1;
        if (is_array($xml_arr)) {
            foreach ($xml_arr as $xml_var_arr) {
                $var = get_morph_vars_inner($xml_var_arr, $i++);
                $t = array();
                foreach ($var['gram_list'] as $gr) {
                    if (!isset($gram_descr[$gr['inner']])) {
                        $r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='".$gr['inner']."' COLLATE utf8_bin LIMIT 1"));
                        $gram_descr[$gr['inner']] = array($r[0], $r[1]);
                    }
                    $t[] = array('inner' => $gr['inner'], 'outer' => $gram_descr[$gr['inner']][0], 'descr' => $gram_descr[$gr['inner']][1]);
                }
                $var['gram_list'] = $t;
                $out[] = $var;
            }
        }
        return $out;
    }
}
function get_morph_vars_inner($xml_arr, $num) {
    $lemma_grm = $xml_arr['_c']['l']['_c']['g'];
    $grm_arr = array();
    if (isset ($lemma_grm['_a']) && is_array($lemma_grm['_a'])) {
        //$r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='$inner_id' LIMIT 1"));
        //array_push($grm_arr, array('inner' => $inner_id, 'outer' => $r[0], 'descr' => $r[1]));
        $grm_arr[] = array('inner' => $lemma_grm['_a']['v']);
    } elseif (is_array($lemma_grm)) {
        foreach ($lemma_grm as $t) {
            //$r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='$inner_id' LIMIT 1"));
            //array_push($grm_arr, array('inner' => $inner_id, 'outer' => $r[0], 'descr' => $r[1]));
            $grm_arr[] = array('inner' => $t['_a']['v']);
        }
    }
    return array(
        'num'        => $num,
        'lemma_id'   => $xml_arr['_c']['l']['_a']['id'],
        'lemma_text' => $xml_arr['_c']['l']['_a']['t'],
        'gram_list'  => $grm_arr
    );
}
function sentence_save($sent_id) {
    if (!$sent_id) return false;
    $flag = $_POST['var_flag'];  //what morphovariants are checked as possible (array of arrays)
    $dict = $_POST['dict_flag']; //whether this token has been reloaded from the dictionary (array)
    $res = sql_query("SELECT tf_id, tf_text, `pos` FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
    while ($r = sql_fetch_array($res)) {
        $rev = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=".$r['tf_id']." AND is_last=1 LIMIT 1"));
        $tokens[$r['tf_id']] = array($r['tf_text'], $rev['rev_text']);
    }
    $matches = array();
    $all_changes = array();
    if (count($flag) != count($tokens))
        return false;

    sql_begin();
    foreach ($tokens as $tf_id=>$v) {
        list($tf_text, $base_xml) = $v;
        //substitute the last revision's xml for one from dictionary if relevant
        if ($dict[$tf_id] == 1) {
            $xml = generate_tf_rev($tf_text);
        } else {
            $xml = $base_xml;
        }
        $new_xml = "<tfr t=\"".htmlspecialchars($tf_text)."\">";
        //let's find all vars inside tf_text
        if (preg_match_all("/<v>(.+?)<\/v>/", $xml, $matches) !== false) {
            //flags quantity check
            if (count($matches[1]) != count($flag[$tf_id]))
                return false;

            $not_empty = 0;
            foreach ($flag[$tf_id] as $k=>$f) {
                if ($f == 1) {
                    $not_empty = 1;
                    $new_xml .= '<v>'.$matches[1][$k-1].'</v>'; //attention to -1
                }
            }
            //inserting UnknownPOS if no variants present
            if (!$not_empty) {
                $new_xml .= '<v><l id="0" t="'.htmlspecialchars(mb_strtolower($tf_text, 'UTF-8')).'"><g v="UNKN"/></l></v>';
            }
            $new_xml .= '</tfr>';
            if ($base_xml != $new_xml) {
                //something's changed
                array_push($all_changes, array($tf_id, $new_xml));
            }
        } else {
            return false;
        }
    }
    if (count($all_changes)>0) {
        $revset_id = create_revset($_POST['comment']);
        if (!$revset_id)
            return false;
        foreach ($all_changes as $v) {
            if (
                !sql_query("UPDATE tf_revisions SET is_last=0 WHERE tf_id=$v[0]") ||
                !sql_query("INSERT INTO `tf_revisions` VALUES(NULL, '$revset_id', '$v[0]', '".mysql_real_escape_string($v[1])."', 1)")
            )
                return false;
        }
    }
    if (sql_query("UPDATE sentences SET check_status='1' WHERE sent_id=$sent_id LIMIT 1")) {
        sql_commit();
        return true;
    }
    return false;
}
// annotation pools
function get_morph_pools_page($type, $my_moder=false) {
    $pools = array();
    $instance_count = array();

    // possible pool types for addition form
    $types = array(0 => 'Новый');
    $res = sql_query("SELECT type_id, grammemes FROM morph_annot_pool_types order by grammemes");
    while ($r = sql_fetch_array($res))
        $types[$r['type_id']] = $r['grammemes'];
    
    // count instances in one query and preserve
    $res = sql_query("SELECT answer, count(instance_id) cnt, pool_id FROM morph_annot_instances LEFT JOIN morph_annot_samples s USING(sample_id) WHERE pool_id IN (SELECT pool_id FROM morph_annot_pools WHERE status = $type) GROUP BY (answer > 0), pool_id ORDER BY pool_id");
    while ($r = sql_fetch_array($res)) {
        if (!isset($instance_count[$r['pool_id']]))
            $instance_count[$r['pool_id']] = array(0, 0, 0);

        if ($r['answer'] > 0)
            $instance_count[$r['pool_id']][0] += $r['cnt'];
        $instance_count[$r['pool_id']][1] += $r['cnt'];
    }
    // and moderated answers if needed
    if ($type == 5) {
        $res = sql_query("SELECT pool_id, COUNT(*) cnt FROM morph_annot_moderated_samples LEFT JOIN morph_annot_samples USING(sample_id) WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id IN (SELECT pool_id FROM morph_annot_pools WHERE status=5)) AND answer > 0 GROUP BY pool_id");
        while ($r = sql_fetch_array($res)) {
            $instance_count[$r['pool_id']][2] = $r['cnt'];
        }
    }

    $q_moder = '';
    if ($my_moder)
        $q_moder = "AND p.moderator_id = ".$_SESSION['user_id'];
    $res = sql_query("SELECT p.*, t.grammemes, t.gram_descr, u1.user_shown_name AS author_name, u2.user_shown_name AS moderator_name FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) LEFT JOIN users u1 ON (p.author_id = u1.user_id) LEFT JOIN users u2 ON (p.moderator_id = u2.user_id) WHERE status = $type $q_moder ORDER BY p.updated_ts DESC");
    while ($r = sql_fetch_assoc($res)) {
        if ($type == 1) {
            $r1 = sql_fetch_array(sql_query("SELECT COUNT(*) FROM morph_annot_candidate_samples WHERE pool_id=".$r['pool_id']));
            $r['candidate_count'] = $r1[0];
        }
        elseif ($type == 5) {
            $r['moderated_count'] = $instance_count[$r['pool_id']][2];
        }

        $r['answer_count'] = $instance_count[$r['pool_id']][0];
        $r['instance_count'] = $instance_count[$r['pool_id']][1];

        $pools[] = $r;
    }
    return array('pools' => $pools, 'types' => $types);
}
function get_morph_samples_page($pool_id, $extended=false, $context_width=4, $filter=false) {
    $res = sql_query("SELECT pool_name, pool_type, status, t.grammemes, users_needed, moderator_id, user_shown_name AS user_name FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) LEFT JOIN users ON (p.moderator_id = users.user_id) WHERE pool_id=$pool_id LIMIT 1");
    $r = sql_fetch_array($res);
    $pool_gram = explode('@', str_replace('&', ' & ', $r['grammemes']));
    $select_options = array('---');
    foreach ($pool_gram as $v) {
        $select_options[] = $v;
    }
    $select_options[99] = 'Other';
    $out = array('id' => $pool_id, 'type' => $r['pool_type'], 'variants' => $select_options, 'name' => $r['pool_name'], 'status' => $r['status'], 'num_users' => $r['users_needed'], 'moderator_name' => $r['user_name']);
    $res = sql_query("SELECT sample_id, tf_id FROM morph_annot_samples WHERE pool_id=$pool_id ORDER BY sample_id");
    $gram_descr = array();
    $distinct_users = array();
    $out['all_moderated'] = $extended ? true : false;  // for now we never get active button with non-extended view, just for code simplicity
    while ($r = sql_fetch_array($res)) {
        $t = get_context_for_word($r['tf_id'], $context_width);
        $t['id'] = $r['sample_id'];
        $r1 = sql_fetch_array(sql_query("SELECT book_id FROM paragraphs WHERE par_id = (SELECT par_id FROM sentences WHERE sent_id = ".$t['sentence_id']." LIMIT 1) LIMIT 1"));
        $t['book_id'] = $r1['book_id'];
        $r1 = sql_fetch_array(sql_query("SELECT COUNT(*) FROM morph_annot_instances WHERE sample_id=".$r['sample_id']." AND answer>0"));
        $t['answered'] = $r1[0];
        if ($extended) {
            $r1 = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id = ".$r['tf_id']." AND is_last=1 LIMIT 1"));
            $arr = xml2ary($r1['rev_text']);
            $t['parses'] = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
            $res1 = sql_query("SELECT instance_id, user_id, answer FROM morph_annot_instances WHERE sample_id=".$r['sample_id']." ORDER BY instance_id");
            $disagreement_flag = false;
            $not_ok_flag = false;
            $vars = '';
            while ($r1 = sql_fetch_array($res1)) {
                if ($r1['answer'] == 99)
                    $disagreement_flag = true;
                elseif (!$vars)
                    $vars = $r1['answer'];
                elseif ($r1['answer'] && $vars != $r1['answer'])
                    $disagreement_flag = true;
                //about users
                if (!isset($distinct_users[$r1['user_id']])) {
                    $r2 = sql_fetch_array(sql_query("SELECT user_shown_name AS user_name FROM users WHERE user_id=".$r1['user_id']." LIMIT 1"));
                    $distinct_users[$r1['user_id']] = array(sizeof($distinct_users), $r2['user_name']);
                }
                //push
                $t['instances'][] = array(
                    'id' => $r1['instance_id'],
                    'answer_num' => $r1['answer'],
                    'answer_gram' => ($r1['answer'] > 0 && $r1['answer'] < 99) ? $pool_gram[$r1['answer']-1] : '',
                    'user_color' => $distinct_users[$r1['user_id']][0]
                );
            }
            $t['disagreed'] = $disagreement_flag;
            $t['comments'] = get_sample_comments($r['sample_id']);
            //for moderators
            if (user_has_permission('perm_check_morph') && $out['status'] > 4) {
                $r1 = sql_fetch_array(sql_query("SELECT answer, status FROM morph_annot_moderated_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1"));
                $t['moder_answer_num'] = $r1['answer'];
                $t['moder_status_num'] = $r1['status'];
                if ($r1['status'] > 0)
                    $not_ok_flag = true;
                if ($t['moder_answer_num'] == 0)
                    $out['all_moderated'] = false;
                else {
                    $t['moder_answer_gram'] = ($r1['answer'] == 99 ? 'Other' : $pool_gram[$r1['answer']-1]);
                    // highlight samples where the moderator disagreed with all the annotators
                    if (!$t['disagreed'] && $t['moder_answer_num'] != $t['instances'][0]['answer_num'])
                        $t['disagreed'] = 1;
                }
            }
        }
        if (
            !$extended ||
            // special list for moderation
            ($filter == 'focus' && (
                $disagreement_flag ||
                sizeof($t['comments']) > 0 ||
                filter_sample_for_moderation($out['type'], $t)
            ))
            ||
            // anything except it
            ($filter != 'focus' &&
            ($disagreement_flag || $filter != 'disagreed') &&
            ($t['moder_answer_num'] == 0 || $filter != 'not_moderated') &&
            (sizeof($t['comments']) > 0 || $filter != 'comments') &&
            ($not_ok_flag || $filter != 'not_ok'))
        )
            $out['samples'][] = $t;
    }
    $out['user_colors'] = $distinct_users;
    $out['filter'] = $filter;
    return $out;
}
function filter_sample_for_moderation($pool_type, $sample) {
    // check all one-symbol focus words
    if (mb_strlen($sample['context'][$sample['mainword']]) == 1)
        return true;
    // disregard context in any pools except NOUN sing-plur
    if ($pool_type != 12)
        return false;
    // focus word with Fixd or Pltm 
    foreach ($sample['parses'] as $parse) {
        foreach ($parse['gram_list'] as $gram) {
            if (in_array($gram['inner'], array('Fixd', 'Pltm')))
                return true;
        }
    }
    // left context with numbers
    for ($i = max(0, $sample['mainword'] - 3); $i < $sample['mainword']; ++$i) {
        if (preg_match('/^(?:\d+|полтор[аы]|дв[ае]|об[ае]|три|четыре)$/u', $sample['context'][$i]))
            return true;
    }
    // nothing suspicious, ok
    return false;
}
function get_pool_candidates_page($pool_id) {
    $pool = array('id' => $pool_id);
    $r = sql_fetch_array(sql_query("SELECT pool_name FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    $pool['name'] = $r['pool_name'];
    $matches = array();
    if (preg_match('/^(.+?)\s+#(\d+)/', $pool['name'], $matches))
        $pool['next_name'] = $matches[1] . ' #';
    else
        $pool['next_name'] = $pool_name . ' #';
    $pool['samples'] = get_pool_candidates($pool_id);
    return $pool;
}
function get_pool_candidates($pool_id) {
    $res = sql_query("SELECT tf_id FROM morph_annot_candidate_samples WHERE pool_id=$pool_id ORDER BY RAND() LIMIT 200");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[] = get_context_for_word($r[0], 2);
    }
    return $out;
}
function get_context_for_word($tf_id, $delta, $dir=0, $include_self=1) {
    // dir stands for direction (-1 => left, 1 => right, 0 => both)
    // delta <= 0 stands for infinity
    $t = array();
    $tw = 0;
    $left_c = -1;  //if there is left context to be added
    $right_c = 0;  //same for right context
    $mw_pos = 0;

    $r = sql_fetch_array(sql_query("SELECT MAX(pos) AS maxpos, sent_id FROM text_forms WHERE sent_id=(SELECT sent_id FROM text_forms WHERE tf_id=$tf_id LIMIT 1)"));
    $sent_id = $r['sent_id'];
    $maxpos = $r['maxpos'];
    $q = "SELECT tf_id, tf_text, pos FROM text_forms WHERE sent_id = $sent_id";
    if ($dir != 0 || $delta > 0) {
        $q_left = $dir <= 0 ? ($delta > 0 ? "(SELECT GREATEST(0, pos-$delta) FROM text_forms WHERE tf_id=$tf_id LIMIT 1)" : "0") : "(SELECT pos FROM text_forms WHERE tf_id=$tf_id LIMIT 1)";
        $q_right = $dir >= 0 ? ($delta > 0 ? "(SELECT pos+$delta FROM text_forms WHERE tf_id=$tf_id LIMIT 1)" : "1000") : "(SELECT pos FROM text_forms WHERE tf_id=$tf_id LIMIT 1)";
        $q .= " AND pos BETWEEN $q_left AND $q_right";
    }

    $q .= " ORDER BY pos";

    $res = sql_query($q);

    while ($r = sql_fetch_array($res)) {
        if ($delta > 0) {
            if ($left_c == -1) {
                $left_c = ($r['pos'] == 1) ? 0 : $r['tf_id'];
            }
            if ($mw_pos) {
                if ($r['pos'] >= $mw_pos + $delta && $r['pos'] < $maxpos)
                    $right_c = $r['tf_id'];
            }
        }

        if ($include_self || $r['tf_id'] != $tf_id)
            $t[] = $r['tf_text'];
        if ($include_self && $r['tf_id'] == $tf_id) {
            $tw = sizeof($t) - 1;
            $mw_pos = $r['pos'];
        }
    }
    return array('context' => $t, 'mainword' => $tw, 'has_left_context' => $left_c, 'has_right_context' => $right_c, 'sentence_id' => $sent_id);
}
function add_morph_pool_type($post_gram, $post_descr) {
    $gram_sets = array();
    $gram_descr = array();
    foreach ($post_gram as $i => $gr) {
        if (!trim($gr))
            break;
        if (strpos($gr, '@') !== false)
            return false;
        $gram_sets[] = str_replace(' ', '', trim($gr));
        $gram_descr[] = trim($_POST['descr'][$i]);
    }
    
    if (sizeof($gram_sets) < 2)
        return false;

    $gram_sets_str = mysql_real_escape_string(join('@', $gram_sets));
    $gram_descr_str = mysql_real_escape_string(join('@', $gram_descr));

    if (sql_query("INSERT INTO morph_annot_pool_types VALUES (NULL, '$gram_sets_str', '$gram_descr_str', '', 0)"))
        return sql_insert_id();
    return false;
}
function add_morph_pool() {
    $pool_name = mysql_real_escape_string(trim($_POST['pool_name']));
    $pool_type = (int)$_POST['pool_type'];
    if (!$pool_type) {
        $pool_type = add_morph_pool_type($_POST['gram'], $_POST['descr']);
        if (!$pool_type)
            return false;
    }

    $users = (int)$_POST['users_needed'];
    $token_check = (int)$_POST['token_checked'];
    $ts = time();
    sql_begin();
    if (sql_query("INSERT INTO morph_annot_pools VALUES(NULL, '$pool_type', '$pool_name', '$token_check', '$users', '$ts', '$ts', '".$_SESSION['user_id']."', '0', '0', '0')")) {
        sql_commit();
        return true;
    }
    return false;
}
function delete_morph_pool($pool_id) {
    //NB: we mustn't delete any pools with answers
    $res = sql_query("SELECT instance_id FROM morph_annot_instances WHERE answer > 0 AND sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id)");
    if (sql_num_rows($res) > 0)
        return false;

    sql_begin();
    if (
        sql_query("DELETE FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id)") &&
        sql_query("DELETE FROM morph_annot_candidate_samples WHERE pool_id=$pool_id") &&
        sql_query("DELETE FROM morph_annot_moderated_samples WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id)") &&
        sql_query("DELETE FROM morph_annot_samples WHERE pool_id=$pool_id") &&
        sql_query("DELETE FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1")
    ) {
        sql_commit();
        return true;
    }
    return false;
}
function promote_samples_aux($tf_ids, $orig_pool_id, $lastrev, $new_pool_name, &$new_pool_index, &$promoted_pools) {
    sql_begin();
    $time = time();

    // the first pool
    if (!$promoted_pools) {
        $current_pool_id = $orig_pool_id;
    }
    // all except the first
    else {
        $full_name = $new_pool_name . ' #' . ($new_pool_index++);
        if (!sql_query("INSERT INTO morph_annot_pools (SELECT NULL, pool_type, '".mysql_real_escape_string($full_name)."', token_check, users_needed, $time, $time, author_id, 0, 2, $lastrev FROM morph_annot_pools WHERE pool_id=$orig_pool_id LIMIT 1)"))
            return false;
        $current_pool_id = sql_insert_id();
    }

    $lsoval = array();
    foreach ($tf_ids as $id)
        $lsoval[] = '(' . join(', ', array('NULL', $current_pool_id, $id)) . ')';

    if (!sql_query("INSERT INTO morph_annot_samples VALUES ".join(', ', $lsoval)))
        return false;

    $promoted_pools[] = $current_pool_id;

    sql_commit();
    return true;
}
function promote_samples($pool_id, $type) {
    if (!$pool_id || !$type) return 0;
    
    $cond = "WHERE pool_id=$pool_id";
    $pools_num = (int)$_POST['pools_num'];
    if (!$pools_num)
        return false;

    switch ($type) {
        case 'first':
            $n = (int)$_POST['first_n'];
            $cond .= " ORDER BY tf_id LIMIT " . ($n * $pools_num);
            break;
        case 'random':
            $n = (int)$_POST['random_n'];
            $cond .= " ORDER BY RAND() LIMIT " . ($n * $pools_num);
            break;
        default:
            return false;
    }

    $r = sql_fetch_array(sql_query("SELECT pool_name, revision FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    $lastrev = $r['revision'];
    if (!$lastrev) {
        $r1 = sql_fetch_array(sql_query("SELECT MAX(rev_id) FROM tf_revisions"));
        $lastrev = $r1[0];
    }

    $matches = array();
    $next_pool_name = '';
    $next_pool_index = '';
    if (preg_match('/^(.+?)\s+#(\d+)/', $r['pool_name'], $matches)) {
        $next_pool_name = $matches[1];
        $next_pool_index = $matches[2] + 1;
    } else {
        $next_pool_name = $r['pool_name'];
        $next_pool_index = 2;
    }

    $time = time();

    sql_begin();
    $res = sql_query("SELECT tf_id FROM morph_annot_candidate_samples $cond");
    $i = 0;
    $tf_array = array();
    $promoted_pool_ids = array();
    while ($r = sql_fetch_array($res)) {
        $tf_array[] = $r['tf_id'];
        if (++$i % $n == 0) {
            if (!promote_samples_aux($tf_array, $pool_id, $lastrev, $next_pool_name, $next_pool_index, $promoted_pool_ids))
                return false;
            $tf_array = array();
        }
    }
    if ($tf_array && !promote_samples_aux($tf_array, $pool_id, $lastrev, $next_pool_name, $next_pool_index, $promoted_pool_ids))
            return false;


    if (!sql_query("UPDATE morph_annot_pools SET `status`='2', `revision`='$lastrev', `created_ts`='$time', `updated_ts`='$time' WHERE pool_id=$pool_id LIMIT 1"))
        return false;

    // delete tf_ids that were added
    if (!sql_query("DELETE cs.* FROM morph_annot_candidate_samples cs LEFT JOIN morph_annot_samples s USING(tf_id) WHERE s.pool_id IN (".join(',', $promoted_pool_ids).")"))
        return false;

    if (isset($_POST['keep'])) {
        if (
            !sql_query("INSERT INTO morph_annot_pools (SELECT NULL, pool_type, '".mysql_real_escape_string($next_pool_name . ' #' . $next_pool_index)."', token_check, users_needed, $time, $time, author_id, 0, 1, $lastrev FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1)") ||
            !sql_query("UPDATE morph_annot_candidate_samples SET pool_id=".sql_insert_id()." WHERE pool_id=$pool_id")
        )
            return false;
    }

    if (sql_query("DELETE FROM morph_annot_candidate_samples WHERE pool_id=$pool_id")) {
        sql_commit();
        return true;
    }
    return false;
}
function publish_pool($pool_id) {
    if (!$pool_id) return false;

    $r = sql_fetch_array(sql_query("SELECT `status`, users_needed FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    sql_begin();

    if ($r['status'] < 3) {
        //all this should be done only if the pool is published for the 1st time
        $N = $r['users_needed'];
        for ($i = 0; $i < $N; ++$i) {
            if (!sql_query("INSERT INTO morph_annot_instances(SELECT NULL, sample_id, 0, 0, 0 FROM morph_annot_samples WHERE pool_id=$pool_id ORDER BY sample_id)")) {
                return false;
            }
        }
        if (!sql_query("INSERT INTO morph_annot_moderated_samples (SELECT sample_id, 0, 0, 0, 0 FROM morph_annot_samples WHERE pool_id=$pool_id ORDER BY sample_id)")) {
            return false;
        }
    }

    if (sql_query("UPDATE morph_annot_pools SET `status`='3', `updated_ts`='".time()."' WHERE pool_id=$pool_id LIMIT 1")) {
        sql_commit();
        return true;
    }
    return false;
}
function unpublish_pool($pool_id) {
    if (!$pool_id) return false;

    if (sql_query("UPDATE morph_annot_pools SET `status`='4', `updated_ts`='".time()."' WHERE pool_id=$pool_id LIMIT 1"))
        return true;
    return false;
}
function moderate_pool($pool_id) {
    if (!$pool_id) return false;

    //we should only allow to moderate pools once we have all the answers
    $res = sql_query("SELECT instance_id FROM morph_annot_instances WHERE answer=0 AND sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id)");
    if (sql_num_rows($res) > 0)
        return false;

    if (sql_query("UPDATE morph_annot_pools SET `status`='5', `updated_ts`='".time()."' WHERE pool_id=$pool_id LIMIT 1"))
        return true;
    return false;
}
function finish_moderate_pool($pool_id) {
    if (!$pool_id) return false;

    //only the pool moderator can finish moderation
    $r = sql_fetch_array(sql_query("SELECT moderator_id FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    if ($r['moderator_id'] != $_SESSION['user_id'])
        return false;

    //we cannot finish unless all the samples are moderated
    $res = sql_query("SELECT sample_id FROM morph_annot_moderated_samples WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND answer = 0 LIMIT 1");
    if (sql_num_rows($res) > 0)
        return false;

    return (bool)sql_query("UPDATE morph_annot_pools SET status=6, updated_ts=".time()." WHERE pool_id=$pool_id LIMIT 1");
}
function begin_pool_merge($pool_id) {
    if (!$pool_id || !is_admin())
        return false;

    // we can perform this only if pool has been moderated
    $r = sql_fetch_array(sql_query("SELECT status FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    if ($r['status'] != 6)
        return false;

    return (bool)sql_query("UPDATE morph_annot_pools SET status=7, updated_ts=".time()." WHERE pool_id=$pool_id LIMIT 1");
}
function get_available_tasks($user_id, $only_editable=false, $limit=0, $random=false) {
    $tasks = array();

    if ($random)
        $order_string = "ORDER BY RAND()";
    else
        $order_string = "ORDER BY (complexity > 0) DESC, complexity, pool_type, created_ts";

    if ($limit)
        $limit_string = "LIMIT " . (2 * $limit);
    else
        $limit_string = "";

    $time = time();
    $cnt = 0;
    $pools = array();
    // memorize pool types with manual and complexity
    $types_with_manual = array();
    $type2complexity = array();
    $res = sql_query("SELECT type_id, doc_link, complexity FROM morph_annot_pool_types WHERE doc_link != '' OR complexity > 0");
    while ($r = sql_fetch_array($res)) {
        if ($r['doc_link'])
            $types_with_manual[] = $r['type_id'];
        if ($r['complexity'] > 0)
            $type2complexity[$r['type_id']] = $r['complexity'];
    }
    // get all pools by status
    $res = sql_query("SELECT pool_id, pool_name, status, pool_type FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) WHERE status = 3 $order_string $limit_string");
    while ($r = sql_fetch_array($res)) {
        $pools[$r['pool_id']] = array('id' => $r['pool_id'], 'name' => $r['pool_name'], 'status' => $r['status'], 'num_started' => 0, 'num_done' => 0, 'num' => 0, 'group' => $r['pool_type']);
    }

    if (!$pools)
        return $tasks;

    $pool_ids = array_keys($pools);
    // get sample counts for selected pools
    // gather count of all available samples grouped by pool
    $r_rejected_samples = sql_query("SELECT sample_id FROM morph_annot_rejected_samples WHERE user_id=$user_id");
    $rejected = array(0);
    while ($r = sql_fetch_array($r_rejected_samples))
        $rejected[] = $r['sample_id'];

    $r_available_samples = sql_query('
        SELECT pool_id,count(distinct sample_id) as cnt
        FROM morph_annot_instances 
        LEFT JOIN morph_annot_samples USING(sample_id) 
        WHERE 
            answer=0 
            AND ts_finish < ' . $time . '
            AND pool_id IN (' . implode(', ',$pool_ids) . ')
            AND sample_id NOT IN ('. join(',', $rejected).')
            AND sample_id NOT IN (
                SELECT sample_id 
                FROM morph_annot_instances 
                WHERE user_id=' . $user_id . ') 
        GROUP BY pool_id');
    while ($available_samples = sql_fetch_array($r_available_samples)) {
        $pools[$available_samples['pool_id']]['num'] = $available_samples['cnt'];
    }
    // gather count of all samples with started instances with empty answer grouped by pool
    $r_started_samples = sql_query('
        SELECT pool_id, count(*) as cnt 
        FROM morph_annot_instances 
        LEFT JOIN morph_annot_samples USING(sample_id) 
        WHERE user_id=' . $user_id . '
            AND morph_annot_instances.answer=0 
            AND pool_id IN (' . implode(',',$pool_ids) . ')
        GROUP BY pool_id');
    while ($started_samples = sql_fetch_array($r_started_samples)) {
        $pools[$started_samples['pool_id']]['num_started'] = $started_samples['cnt'];
    }
    // gather count of all samples with instance & answer grouped by pool
    $r_done_samples = sql_query('
        SELECT pool_id, count(*) as cnt 
        FROM morph_annot_instances 
        LEFT JOIN morph_annot_samples USING(sample_id) 
        WHERE user_id=' . $user_id . '
            AND morph_annot_instances.answer>0 
            AND pool_id IN (' . implode(',', $pool_ids) . ')
        GROUP BY pool_id');
    while ($done_samples = sql_fetch_array($r_done_samples)) {
        $pools[$done_samples['pool_id']]['num_done'] = $done_samples['cnt'];
    }
    foreach ($pools as $pool) {
        if (
            // we are not interested in not available & not started pools
            $pool['num'] + $pool['num_started'] + $pool['num_done'] > 0 &&
            // we may be as well not interested in pools where nothing remains to do
            (!$only_editable || ($pool['num'] + $pool['num_started']) > 0)
        ) {
            if ($random)
                $tasks[] = $pool;
            else
                $tasks[$pool['group']]['pools'][] = $pool;

            ++$cnt;
            if ($limit > 0 && $cnt == $limit)
                break;
        }
    }

    if (!$random)
        foreach ($tasks as $group_id => $v) {
            $i = 0;
            while ($i < sizeof($v['pools'])) {
                if ($v['pools'][$i]['num'] + $v['pools'][$i]['num_started'] > 0) {
                    $tasks[$group_id]['first_id'] = $v['pools'][$i]['id'];
                    break;
                }
                ++$i;
            }
            if (isset($tasks[$group_id]['first_id']))
                while (true) {
                    $rand = mt_rand(0, sizeof($v['pools']) - 1);
                    if ($v['pools'][$rand]['num'] + $v['pools'][$rand]['num_started'] > 0) {
                        $tasks[$group_id]['random_id'] = $v['pools'][$rand]['id'];
                        break;
                    }
                }
            $tasks[$group_id]['has_manual'] = in_array($group_id, $types_with_manual);
            $tasks[$group_id]['complexity'] = isset($type2complexity[$group_id]) ? $type2complexity[$group_id] : 0;
            $tasks[$group_id]['name'] = preg_replace('/\s+#\d+\s*$/', '', $v['pools'][0]['name']);
        }

    return $tasks;
}
function get_my_answers($pool_id, $limit=10, $skip=0) {
    // TODO: we may certainly refactor here: this and get_annotation_packet() should share code
    $packet = array('my' => 1);
    $r = sql_fetch_array(sql_query("SELECT status, t.gram_descr FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) WHERE pool_id=$pool_id"));
    if ($r['status'] != 3)
        $packet['editable'] = 0;
    else
        $packet['editable'] = 1;
    $packet['gram_descr'] = explode('@', $r['gram_descr']);
    $user_id = $_SESSION['user_id'];

    $limit_str = '';
    if ($limit)
        $limit_str = " LIMIT $skip, $limit";
    $res = sql_query("SELECT instance_id, sample_id, answer FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND user_id=$user_id AND answer>0 $limit_str");
    if (!sql_num_rows($res)) return false;

    $gram_descr = array();
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT tf_id, rev_text FROM tf_revisions WHERE tf_id = (SELECT tf_id FROM morph_annot_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1) AND is_last=1 LIMIT 1"));
        $instance = get_context_for_word($r1['tf_id'], 4);
        $arr = xml2ary($r1['rev_text']);
        $parses = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
        $lemmata = array();
        foreach ($parses as $p) {
            $lemmata[] = $p['lemma_text'];
        }
        $instance['lemmata'] = implode(', ', array_unique($lemmata));
        $instance['id'] = $r['instance_id'];
        $instance['sample_id'] = $r['sample_id'];
        $instance['answer'] = $r['answer'];
        $packet['instances'][] = $instance;
    }
    return $packet;
}
function get_next_pool($user_id, $prev_pool_id) {
    if (!$user_id || !$prev_pool_id)
        return false;

    $time = time();
    $res = sql_query("SELECT pool_id FROM morph_annot_pools WHERE status = 3 AND pool_type = (SELECT pool_type FROM morph_annot_pools WHERE pool_id=$prev_pool_id LIMIT 1) ORDER BY created_ts");
    while ($r = sql_fetch_array($res)) {
        $res1 = sql_query("
            SELECT instance_id FROM morph_annot_instances LEFT JOIN morph_annot_samples USING (sample_id)
            WHERE answer = 0
            AND pool_id = ".$r['pool_id']."
            AND ts_finish < $time
            AND sample_id NOT IN (
                SELECT sample_id 
                FROM morph_annot_instances 
                WHERE user_id=$user_id)
            AND sample_id NOT IN (
                SELECT sample_id 
                FROM morph_annot_rejected_samples 
                WHERE user_id=$user_id)
            LIMIT 1
        ");
        if (sql_num_rows($res1) > 0)
            return $r['pool_id'];
    }
    return false;
}
function get_annotation_packet($pool_id, $size) {
    $r = sql_fetch_array(sql_query("SELECT status, t.gram_descr, revision, pool_type, doc_link FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) WHERE pool_id=$pool_id"));
    if ($r['status'] != 3) return false;
    $packet = array(
        'my' => 0,
        'editable' => 1,
        'pool_type' => $r['pool_type'],
        'has_manual' => (bool)$r['doc_link'],
        'gram_descr' => explode('@', $r['gram_descr'])
    );
    $user_id = $_SESSION['user_id'];
    $flag_new = 0;
    $pool_revision = $r['revision'];

    //if the user has something already reserved, let's start with that (but only if the poolid is the same!)
    $res = sql_query("SELECT instance_id, sample_id FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND user_id=$user_id AND answer=0 LIMIT $size");
    if (!sql_num_rows($res)) {
        //ok, we should find new samples
        //first, check non-owned ones
        $res = sql_query("SELECT instance_id, sample_id FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND sample_id NOT IN (SELECT DISTINCT sample_id FROM morph_annot_instances WHERE user_id=$user_id) AND sample_id NOT IN (SELECT sample_id FROM morph_annot_rejected_samples WHERE user_id=$user_id) AND ts_finish=0 AND answer=0 GROUP BY sample_id LIMIT $size");
        $flag_new = 1;
        if (!sql_num_rows($res)) {
            $time = time();
            //if nothing found, check owned but outdated ones
            $res = sql_query("SELECT instance_id, sample_id FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND sample_id NOT IN (SELECT DISTINCT sample_id FROM morph_annot_instances WHERE user_id=$user_id) AND sample_id NOT IN (SELECT sample_id FROM morph_annot_rejected_samples WHERE user_id=$user_id) AND answer=0 AND ts_finish < $time GROUP BY sample_id LIMIT $size");
        }
    }
    if (!sql_num_rows($res)) return false;

    //when the timeout will be - same for each sample
    $ts_finish = time() +  600;
    if ($flag_new) sql_begin();
    $gram_descr = array();
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT tf_id, rev_text FROM tf_revisions WHERE tf_id = (SELECT tf_id FROM morph_annot_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1) AND rev_id <= $pool_revision ORDER BY rev_id DESC LIMIT 1"));
        $instance = get_context_for_word($r1['tf_id'], 4);
        $arr = xml2ary($r1['rev_text']);
        $parses = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
        $lemmata = array();
        foreach ($parses as $p) {
            $lemmata[] = $p['lemma_text'];
        }
        $instance['lemmata'] = implode(', ', array_unique($lemmata));
        $instance['id'] = $r['instance_id'];
        $instance['sample_id'] = $r['sample_id'];
        $packet['instances'][] = $instance;
        if ($flag_new) {
            if (!sql_query("UPDATE morph_annot_instances SET user_id='$user_id', ts_finish='$ts_finish' WHERE instance_id= ".$r['instance_id']." LIMIT 1")) return false;
        }
    }
    if ($flag_new) sql_commit();
    return $packet;
}
function update_annot_instance($id, $answer) {
    $user_id = $_SESSION['user_id'];
    if (!$id || !$answer || !$user_id) return 0;

    // the pool should be editable
    $r = sql_fetch_array(sql_query("SELECT pool_id, `status` FROM morph_annot_pools WHERE pool_id = (SELECT pool_id FROM morph_annot_samples WHERE sample_id=(SELECT sample_id FROM morph_annot_instances WHERE instance_id=$id LIMIT 1) LIMIT 1)"));
    if ($r['status'] != 3) return 0;

    $pool_id = $r['pool_id'];

    sql_begin();

    // does the instance really belong to this user?
    $r = sql_fetch_array(sql_query("SELECT user_id, answer FROM morph_annot_instances WHERE instance_id=$id LIMIT 1"));
    $previous_answer = $r['answer'] > 0;
    if ($r['user_id'] != $user_id) {
        // if another user has taken it, no chance
        if ($r['user_id'] > 0)
            return 0;
        
        // or, perhaps, this user has rejected it before but has changed his mind
        $res = sql_query("SELECT sample_id FROM morph_annot_rejected_samples WHERE user_id=$user_id AND sample_id = (SELECT sample_id FROM morph_annot_instances WHERE instance_id=$id LIMIT 1) LIMIT 1");
        if (sql_num_rows($res) > 0) {
            $r = sql_fetch_array($res);
            if (!sql_query("DELETE FROM morph_annot_rejected_samples WHERE user_id=$user_id AND sample_id = ".$r['sample_id']." LIMIT 1") ||
                !sql_query("UPDATE morph_annot_instances SET user_id=$user_id, ts_finish=".(time() + 600)." WHERE instance_id=$id LIMIT 1"))
                return 0;
        }
    }

    include_once('lib_awards.php');

    // a valid answer
    if ($answer > 0) {
        if (!sql_query("UPDATE morph_annot_instances SET answer='$answer' WHERE instance_id=$id LIMIT 1") ||
            !update_user_rating($user_id, $pool_id, false, $previous_answer))
            return 0;
    }
    // or a rejected question
    elseif ($answer == -1) {
        if (
            !sql_query("INSERT INTO morph_annot_rejected_samples (SELECT sample_id, $user_id FROM morph_annot_instances WHERE instance_id=$id LIMIT 1)") ||
            !sql_query("UPDATE morph_annot_instances SET user_id='0', ts_finish='0', answer='0' WHERE instance_id=$id LIMIT 1") ||
            !update_user_rating($user_id, $pool_id, true, $previous_answer)
        ) return 0;
    }
    sql_commit();
    return 1;
}
function check_moderator_right($user_id, $pool_id, $make_owner=false) {
    //the pool must have status=5 (under moderation) AND either have no moderator or have this user as moderator
    $r = sql_fetch_array(sql_query("SELECT `status`, moderator_id FROM morph_annot_pools WHERE pool_id = $pool_id LIMIT 1"));
    if ($r['status'] != 5)
        return false;
    sql_begin();
    if ($r['moderator_id'] == 0) {
        if ($make_owner && !sql_query("UPDATE morph_annot_pools SET moderator_id=$user_id WHERE pool_id = $pool_id LIMIT 1"))
            return false;
    } elseif ($r['moderator_id'] != $user_id)
        return false;
    sql_commit();
    return true;
}
function save_moderated_answer($id, $answer, $manual, $field_name='answer') {
    $user_id = $_SESSION['user_id'];
    if (!$id || !$user_id || $answer < 0) return 0;
    $r = sql_fetch_array(sql_query("SELECT pool_id FROM morph_annot_samples WHERE sample_id = $id LIMIT 1"));
    $pool_id = $r['pool_id'];

    if (!check_moderator_right($user_id, $pool_id, true))
        return 0;

    sql_begin();

    if (sql_query("UPDATE morph_annot_moderated_samples SET user_id=$user_id, `$field_name`=$answer, `manual`=$manual WHERE sample_id=$id LIMIT 1")) {
        sql_commit();
        if ($field_name != 'answer')
            return 1;
        //check whether it was the last sample to be moderated
        $res = sql_query("SELECT sample_id FROM morph_annot_moderated_samples WHERE pool_id=$pool_id AND answer = 0 LIMIT 1");
        if (sql_num_rows($res) == 0)
            return 2;
        return 1;
    }
    return 0;
}
function save_moderated_status($id, $status) {
    return save_moderated_answer($id, $status, 1, 'status');
}
function get_sample_comments($sample_id) {
    $res = sql_query("SELECT comment_id, user_shown_name AS user_name, timestamp, text FROM morph_annot_comments LEFT JOIN users USING(user_id) WHERE sample_id=$sample_id ORDER BY timestamp");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[] = array(
            'id' => $r['comment_id'],
            'author' => $r['user_name'],
            'timestamp' => $r['timestamp'],
            'text' => $r['text']
        );
    }
    return $out;
}
function log_click($sample_id, $type) {
    $user_id = $_SESSION['user_id'];
    if (!$sample_id || !$type || !$user_id) return false;

    if ($type == -1) $type = 77;

    $ts = time();
    if (sql_query("INSERT INTO morph_annot_click_log VALUES('$sample_id', '$user_id', '$ts', '$type')"))
        return true;
    return false;
}
function count_all_answers() {
    $res = sql_query("SELECT COUNT(*) FROM morph_annot_instances WHERE answer > 0");
    $r = sql_fetch_array($res);
    return $r[0];
}
function get_pool_manual_page($type_id) {
    $r = sql_fetch_array(sql_query("SELECT doc_link FROM morph_annot_pool_types WHERE type_id=$type_id LIMIT 1"));
    return $r['doc_link'];
}
?>
