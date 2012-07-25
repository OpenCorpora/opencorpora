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
    $res = sql_query("SELECT tf_id, tf_text, dict_updated FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
    $j = 0; //token position, for further highlighting
    $gram_descr = array();  //associative array to keep info about grammemes
    while ($r = sql_fetch_array($res)) {
        array_push ($tf_text, '<span id="src_token_'.($j++).'">'.$r['tf_text'].'</span>');
        $rev = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=".$r['tf_id']." ORDER BY rev_id DESC LIMIT 1"));
        $arr = xml2ary($rev['rev_text']);

        $out['tokens'][] = array(
            'tf_id'        => $r['tf_id'],
            'tf_text'      => $r['tf_text'],
            'dict_updated' => $r['dict_updated'],
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
                $r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='".$gr['inner']."' LIMIT 1"));
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
                        $r = sql_fetch_array(sql_query("SELECT outer_id, gram_descr FROM gram WHERE inner_id='".$gr['inner']."' LIMIT 1"));
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
        $rev = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=".$r['tf_id']." ORDER BY rev_id DESC LIMIT 1"));
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
            //and reset the flag! perhaps it would be better to reset all of them by one query, but seems the case is rather rare
            if (!sql_query("UPDATE text_forms SET dict_updated='0' WHERE tf_id=$tf_id LIMIT 1")) {
                return false;
            }
        } else {
            $xml = $base_xml;
        }
        $new_xml = "<tfr t=\"$tf_text\">";
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
            if (!sql_query("INSERT INTO `tf_revisions` VALUES(NULL, '$revset_id', '$v[0]', '".mysql_real_escape_string($v[1])."')"))
                return false;
        }
    }
    if (sql_query("UPDATE sentences SET check_status='1' WHERE sent_id=$sent_id LIMIT 1")) {
        sql_commit();
        return true;
    }
    return false;
}
//annotation pools
function get_morph_pools_page($type) {
    $pools = array();
    $instance_count = array();
    
    //count instances in one query and preserve
    $res = sql_query("SELECT answer, count(instance_id) cnt, pool_id FROM morph_annot_instances LEFT JOIN morph_annot_samples s USING(sample_id) WHERE pool_id IN (SELECT pool_id FROM morph_annot_pools WHERE status = $type) GROUP BY (answer > 0), pool_id ORDER BY pool_id");
    while ($r = sql_fetch_array($res)) {
        if (!isset($instance_count[$r['pool_id']]))
            $instance_count[$r['pool_id']] = array(0, 0, 0);

        if ($r['answer'] > 0)
            $instance_count[$r['pool_id']][0] += $r['cnt'];
        $instance_count[$r['pool_id']][1] += $r['cnt'];
    }
    //and moderated answers if needed
    if ($type == 5) {
        $res = sql_query("SELECT pool_id, COUNT(*) cnt FROM morph_annot_moderated_samples LEFT JOIN morph_annot_samples USING(sample_id) WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id IN (SELECT pool_id FROM morph_annot_pools WHERE status=5)) AND answer > 0 GROUP BY pool_id");
        while ($r = sql_fetch_array($res)) {
            $instance_count[$r['pool_id']][2] = $r['cnt'];
        }
    }

    $res = sql_query("SELECT p.*, u1.user_shown_name AS author_name, u2.user_shown_name AS moderator_name FROM morph_annot_pools p LEFT JOIN users u1 ON (p.author_id = u1.user_id) LEFT JOIN users u2 ON (p.moderator_id = u2.user_id) WHERE status = $type ORDER BY p.updated_ts DESC");
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
    return $pools;
}
function get_morph_samples_page($pool_id, $extended=false, $only_disagreed=false, $only_not_moderated=false) {
    $res = sql_query("SELECT pool_name, status, grammemes, users_needed, moderator_id, user_shown_name AS user_name FROM morph_annot_pools p LEFT JOIN users ON (p.moderator_id = users.user_id) WHERE pool_id=$pool_id LIMIT 1");
    $r = sql_fetch_array($res);
    $pool_gram = explode('@', str_replace('&', ' & ', $r['grammemes']));
    $select_options = array('---');
    foreach ($pool_gram as $v) {
        $select_options[] = $v;
    }
    $select_options[99] = 'Other';
    $out = array('id' => $pool_id, 'variants' => $select_options, 'name' => $r['pool_name'], 'status' => $r['status'], 'num_users' => $r['users_needed'], 'moderator_name' => $r['user_name']);
    $res = sql_query("SELECT sample_id, tf_id FROM morph_annot_samples WHERE pool_id=$pool_id ORDER BY sample_id");
    $gram_descr = array();
    $distinct_users = array();
    $out['all_moderated'] = $extended ? true : false;  // for now we never get active button with non-extended view, just for code simplicity
    while ($r = sql_fetch_array($res)) {
        $t = get_context_for_word($r['tf_id'], 4);
        $t['id'] = $r['sample_id'];
        $r1 = sql_fetch_array(sql_query("SELECT book_id FROM paragraphs WHERE par_id = (SELECT par_id FROM sentences WHERE sent_id = ".$t['sentence_id']." LIMIT 1) LIMIT 1"));
        $t['book_id'] = $r1['book_id'];
        $r1 = sql_fetch_array(sql_query("SELECT COUNT(*) FROM morph_annot_instances WHERE sample_id=".$r['sample_id']." AND answer>0"));
        $t['answered'] = $r1[0];
        if ($extended) {
            $r1 = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id = ".$r['tf_id']." ORDER BY rev_id DESC LIMIT 1"));
            $arr = xml2ary($r1['rev_text']);
            $t['parses'] = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
            $res1 = sql_query("SELECT instance_id, user_id, answer FROM morph_annot_instances WHERE sample_id=".$r['sample_id']." ORDER BY instance_id");
            $disagreement_flag = 0;
            $vars = '';
            while ($r1 = sql_fetch_array($res1)) {
                if ($r1['answer'] == 99)
                    $disagreement_flag = 1;
                elseif (!$vars)
                    $vars = $r1['answer'];
                elseif ($r1['answer'] && $vars != $r1['answer'])
                    $disagreement_flag = 1;
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
                $r1 = sql_fetch_array(sql_query("SELECT answer FROM morph_annot_moderated_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1"));
                $t['moder_answer_num'] = $r1['answer'];
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
            ($disagreement_flag || !$only_disagreed) &&
            ($t['moder_answer_num'] == 0 || !$only_not_moderated)
        )
            $out['samples'][] = $t;
    }
    $out['user_colors'] = $distinct_users;
    return $out;
}
function get_pool_candidates_page($pool_id) {
    $pool = array('id' => $pool_id);
    $r = sql_fetch_array(sql_query("SELECT pool_name FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    $pool['name'] = $r['pool_name'];
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
function add_morph_pool() {
    $pool_name = mysql_real_escape_string(trim($_POST['pool_name']));
    $gram_sets = array();
    $gram_descr = array();
    foreach ($_POST['gram'] as $i => $gr) {
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
    $comment = mysql_real_escape_string(trim($_POST['comment']));
    $users = (int)$_POST['users_needed'];
    $token_check = (int)$_POST['token_checked'];
    $ts = time();
    sql_begin();
    if (sql_query("INSERT INTO morph_annot_pools VALUES(NULL, '$pool_name', '$gram_sets_str', '$gram_descr_str', '$token_check', '$users', '$ts', '$ts', '".$_SESSION['user_id']."', '0', '0', '0', '$comment')")) {
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
function promote_samples($pool_id, $type) {
    if (!$pool_id || !$type) return 0;
    
    $cond = "WHERE pool_id=$pool_id";
    switch ($type) {
        case 'first':
            $n = (int)$_POST['first_n'];
            $cond .= " ORDER BY tf_id LIMIT $n";
            break;
        case 'random':
            $n = (int)$_POST['random_n'];
            $cond .= " ORDER BY RAND() LIMIT $n";
            break;
        default:
            return false;
    }

    $r = sql_fetch_array(sql_query("SELECT revision FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    $lastrev = $r['revision'];
    if (!$lastrev) {
        $r = sql_fetch_array(sql_query("SELECT MAX(rev_id) FROM tf_revisions"));
        $lastrev = $r[0];
    }

    $time = time();
    sql_begin();
    if (
        sql_query("INSERT INTO morph_annot_samples(SELECT NULL, pool_id, tf_id FROM morph_annot_candidate_samples $cond)") &&
        sql_query("UPDATE morph_annot_pools SET `status`='2', `revision`='$lastrev', `created_ts`='$time', `updated_ts`='$time' WHERE pool_id=$pool_id LIMIT 1") &&
        sql_query("DELETE FROM morph_annot_candidate_samples WHERE tf_id IN (SELECT tf_id FROM morph_annot_samples WHERE pool_id=$pool_id)") &&
        (
            !isset($_POST['keep']) ||
            (
                sql_query("INSERT INTO morph_annot_pools (SELECT NULL, '".mysql_real_escape_string($_POST['next_pool_name'])."', grammemes, gram_descr, token_check, users_needed, $time, $time, author_id, 0, 1, $lastrev, comment FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1)") &&
                sql_query("UPDATE morph_annot_candidate_samples SET pool_id=".sql_insert_id()." WHERE pool_id=$pool_id")
            )
        ) &&
        sql_query("DELETE FROM morph_annot_candidate_samples WHERE pool_id=$pool_id")
    ) {
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
        if (!sql_query("INSERT INTO morph_annot_moderated_samples (SELECT sample_id, 0, 0, 0 FROM morph_annot_samples WHERE pool_id=$pool_id ORDER BY sample_id)")) {
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
function get_available_tasks($user_id, $only_editable=false, $limit=0) {
    $tasks = array();

    if ($only_editable)
        $status_string = "WHERE status = 3";
    else
        $status_string = "WHERE status > 2";

    $time = time();
    $cnt = 0;
    $pools = array();
    // get all pools by status
    $res = sql_query("SELECT pool_id, pool_name, status FROM morph_annot_pools $status_string ORDER BY status, created_ts");
    while ($r = sql_fetch_array($res)) {
        $pools[$r['pool_id']] = array('id' => $r['pool_id'], 'name' => $r['pool_name'], 'status' => $r['status'], 'num_started' => 0, 'num_done' => 0, 'num' => 0);
        /*$pool = array('id' => $r['pool_id'], 'name' => $r['pool_name'], 'status' => $r['status'], 'num_started' => 0, 'num_done' => 0);
        $r1 = sql_fetch_array(sql_query("
            SELECT COUNT(DISTINCT sample_id)
            FROM morph_annot_instances
            WHERE sample_id IN
                (SELECT sample_id
                FROM morph_annot_samples
                WHERE pool_id=".$r['pool_id'].")
            AND answer=0
            AND ts_finish < $time
            AND sample_id NOT IN
                (SELECT DISTINCT sample_id
                FROM morph_annot_instances
                WHERE user_id=$user_id)
            AND sample_id NOT IN
                (SELECT sample_id
                FROM morph_annot_rejected_samples
                WHERE user_id=$user_id)
        "));
        $pool['num'] = $r1[0];
        $res1 = sql_query("SELECT answer, COUNT(instance_id) as cnt FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=".$r['pool_id'].") AND user_id=$user_id GROUP BY (answer > 0)");
        while ($r1 = sql_fetch_array($res1)) {
            if ($r1['answer'] == 0)
                $pool['num_started'] = $r1['cnt'];
            else
                $pool['num_done'] = $r1['cnt'];
        }
        if ($pool['num'] + $pool['num_started'] + $pool['num_done'] > 0) {
            $tasks[] = $pool;

            ++$cnt;
            if ($limit > 0 && $cnt == $limit)
                break;
        }*/
    }
    if ($pools) {
        $pool_ids = array_keys($pools);
        // get sample counts for selected pools
        // gather count of all aveilable samples grouped by pool
        $r_available_samples = sql_query('
            SELECT pool_id,count(distinct sample_id) as cnt
            FROM morph_annot_instances 
            LEFT JOIN morph_annot_samples USING(sample_id) 
            WHERE 
                answer=0 
                AND ts_finish < ' . $time . '
                AND pool_id IN (' . implode(',',$pool_ids) . ')
                AND sample_id NOT IN (
                    SELECT sample_id 
                    FROM morph_annot_instances 
                    WHERE user_id=' . $user_id . ') 
                AND sample_id NOT IN (
                    SELECT sample_id 
                    FROM morph_annot_rejected_samples 
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
                AND pool_id IN (' . implode(',',$pool_ids) . ')
            GROUP BY pool_id');
        while ($done_samples = sql_fetch_array($r_done_samples)) {
            $pools[$done_samples['pool_id']]['num_done'] = $done_samples['cnt'];
        }
        foreach ($pools as $pool) {
            // we are not interested in not available & not started pools
            if ($pool['num'] + $pool['num_started'] + $pool['num_done'] > 0) {
                $tasks[] = $pool;

                ++$cnt;
                if ($limit > 0 && $cnt == $limit)
                    break;
            }
        }
    }

    return $tasks;
}
function get_my_answers($pool_id, $limit=10, $skip=0) {
    // TODO: we may certainly refactor here: this and get_annotation_packet() should share code
    $packet = array('my' => 1);
    $r = sql_fetch_array(sql_query("SELECT status, gram_descr FROM morph_annot_pools WHERE pool_id=$pool_id"));
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
        $r1 = sql_fetch_array(sql_query("SELECT tf_id, rev_text FROM tf_revisions WHERE tf_id = (SELECT tf_id FROM morph_annot_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1) ORDER BY rev_id DESC LIMIT 1"));
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
function get_annotation_packet($pool_id, $size) {
    $packet = array('my' => 0);
    $r = sql_fetch_array(sql_query("SELECT status, gram_descr FROM morph_annot_pools WHERE pool_id=$pool_id"));
    if ($r['status'] != 3) return false;
    $packet['editable'] = 1;
    $packet['gram_descr'] = explode('@', $r['gram_descr']);
    $user_id = $_SESSION['user_id'];
    $flag_new = 0;

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
        $r1 = sql_fetch_array(sql_query("SELECT tf_id, rev_text FROM tf_revisions WHERE tf_id = (SELECT tf_id FROM morph_annot_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1) ORDER BY rev_id DESC LIMIT 1"));
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
    $r = sql_fetch_array(sql_query("SELECT `status` FROM morph_annot_pools WHERE pool_id = (SELECT pool_id FROM morph_annot_samples WHERE sample_id=(SELECT sample_id FROM morph_annot_instances WHERE instance_id=$id LIMIT 1) LIMIT 1)"));
    if ($r['status'] != 3) return 0;

    // does the instance really belong to this user?
    $res = sql_query("SELECT instance_id FROM morph_annot_instances WHERE instance_id=$id AND user_id=$user_id LIMIT 1");
    if (!sql_num_rows($res)) return 0;

    sql_begin();
    // a valid answer
    if ($answer > 0) {
        if (!sql_query("UPDATE morph_annot_instances SET answer='$answer' WHERE instance_id=$id LIMIT 1")) return 0;
    }
    // or a rejected question
    elseif ($answer == -1) {
        if (
            !sql_query("INSERT INTO morph_annot_rejected_samples (SELECT sample_id, $user_id FROM morph_annot_instances WHERE instance_id=$id LIMIT 1)") ||
            !sql_query("UPDATE morph_annot_instances SET user_id='0', ts_finish='0', answer='0' WHERE instance_id=$id LIMIT 1")
        ) return 0;
    }
    sql_commit();
    return 1;
}
function save_moderated_answer($id, $answer) {
    $user_id = $_SESSION['user_id'];
    if (!$id || !$user_id || $answer < 0) return 0;

    //the pool must have status=5 (under moderation) AND either have no moderator or have this user as moderator
    $r = sql_fetch_array(sql_query("SELECT `status`, moderator_id FROM morph_annot_pools WHERE pool_id = (SELECT pool_id FROM morph_annot_samples WHERE sample_id=$id LIMIT 1)"));
    if ($r['status'] != 5)
        return 0;
    sql_begin();
    if ($r['moderator_id'] == 0) {
        if (!sql_query("UPDATE morph_annot_pools SET moderator_id=$user_id WHERE pool_id = (SELECT pool_id FROM morph_annot_samples WHERE sample_id=$id LIMIT 1) LIMIT 1"))
            return 0;
    } elseif ($r['moderator_id'] != $user_id)
        return 0;

    if (sql_query("UPDATE morph_annot_moderated_samples SET user_id=$user_id, answer=$answer WHERE sample_id=$id LIMIT 1")) {
        sql_commit();
        //check whether it was the last sample to be moderated
        $res = sql_query("SELECT sample_id FROM morph_annot_moderated_samples WHERE pool_id=(SELECT pool_id FROM morph_annot_samples WHERE sample_id=$id LIMIT 1) AND answer = 0 LIMIT 1");
        if (sql_num_rows($res) == 0)
            return 2;
        return 1;
    }
    return 0;
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
?>
