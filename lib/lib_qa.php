<?php
function get_page_tok_strange($newest = false) {
    $res = sql_query("SELECT timestamp, param_value FROM stats_values WHERE param_id=7 ORDER BY timestamp DESC LIMIT 1");
    $r = sql_fetch_array($res);
    $out = array('timestamp' => $r['timestamp'], 'coeff' => $r['param_value']/1000);
    $res = sql_query("SELECT timestamp, param_value FROM stats_values WHERE param_id=28 ORDER BY timestamp DESC LIMIT 1");
    if (sql_num_rows($res) > 0) {
        $r = sql_fetch_array($res);
        $tid = $r['param_value'];
        $r = sql_fetch_array(sql_query("SELECT tf_text, sent_id FROM text_forms WHERE tf_id=$tid LIMIT 1"));
        $out['broken_token_text'] = $r['tf_text'];
        $out['broken_sent_id'] = $r['sent_id'];
    }
    $comments = array();
    $res = sql_query("SELECT ts.sent_id, ts.pos, ts.border, ts.coeff, s.source, p.book_id FROM tokenizer_strange ts LEFT JOIN sentences s ON (ts.sent_id=s.sent_id) LEFT JOIN paragraphs p ON (s.par_id=p.par_id) ORDER BY ".($newest ? "ts.sent_id DESC" : "ts.coeff DESC"));
    while ($r = sql_fetch_array($res)) {
        if (!isset($comments[$r['sent_id']])) {
            $res1 = sql_query("SELECT comment_id FROM sentence_comments WHERE sent_id=".$r['sent_id']." LIMIT 1");
            $comments[$r['sent_id']] = sql_num_rows($res1) > 0 ? 1 : -1;
        }
        $out['items'][] = array(
            'sent_id' => $r['sent_id'],
            'book_id' => $r['book_id'],
            'coeff' => $r['coeff'], 
            'border' => $r['border'], 
            'lcontext' => mb_substr($r['source'], max(0, $r['pos']-10), min(10, $r['pos'])),
            'focus' => mb_substr($r['source'], $r['pos'], 1),
            'rcontext' => mb_substr($r['source'], $r['pos']+1, 9),
            'comments' => $comments[$r['sent_id']]
        );
    }
    return $out;
}
function get_page_sent_strange() {
    $out = array();
    $res = sql_query("SELECT sent_id FROM sentences_strange ORDER BY sent_id DESC");
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT source FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1"));
        $r2 = sql_fetch_array(sql_query("SELECT book_id FROM paragraphs WHERE par_id = (SELECT par_id FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1) LIMIT 1"));
        $out[] = array('id' => $r['sent_id'], 'text' => $r1['source'], 'book_id' => $r2['book_id']);
    }
    return $out;
}
function get_empty_books() {
    $res = sql_query("
        SELECT book_id, book_name
        FROM books
        WHERE book_id NOT IN (SELECT DISTINCT book_id FROM paragraphs)
        AND book_id NOT IN (SELECT DISTINCT parent_id FROM books)
    ");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[] = array('id' => $r['book_id'], 'name' => $r['book_name']);
    }
    return $out;
}
function get_downloaded_urls() {
    $res = sql_query("
        SELECT b.book_id, b.book_name, SUBSTR(t.tag_name, 5) url, u.filename
        FROM book_tags t
        LEFT JOIN books b
        ON (t.book_id = b.book_id)
        LEFT JOIN downloaded_urls u
        ON (SUBSTR(t.tag_name, 5) = u.url)
        WHERE t.tag_name LIKE 'url:%'
        ORDER BY b.book_id DESC
    ");
    $out = array();
    while($r = sql_fetch_array($res)) {
        $out[] = array(
            'book_id' => $r['book_id'],
            'book_name' => $r['book_name'],
            'url' => $r['url'],
            'filename' => $r['filename'],
            'exists' => file_exists('files/saved/'.$r['filename'].'.html') ? 1 : 0
        );
    }
    return $out;
}
function get_tag_errors() {
    $res = sql_query("SELECT * FROM tag_errors ORDER BY book_id DESC");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[] = array(
            'book_id' => $r['book_id'],
            'tag_name' => $r['tag_name'],
            'error_type' => $r['error_type']
        );
    }
    return $out;
}
//annotation pools
function get_morph_pools_page() {
    $pools = array();
    $res = sql_query("SELECT p.*, u.user_name FROM morph_annot_pools p LEFT JOIN users u ON (p.author_id = u.user_id) ORDER BY p.updated_ts DESC");
    while($r = sql_fetch_assoc($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT COUNT(*) FROM morph_annot_candidate_samples WHERE pool_id=".$r['pool_id']));
        $r['candidate_count'] = $r1[0];
        $pools[] = $r;
    }
    return $pools;
}
function get_morph_samples_page($pool_id, $extended=false, $only_disagreed=false) {
    $res = sql_query("SELECT pool_name, status, grammemes, users_needed FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1");
    $r = sql_fetch_array($res);
    $pool_gram = explode('@', str_replace('&', ' & ', $r['grammemes']));
    $out = array('id' => $pool_id, 'name' => $r['pool_name'], 'status' => $r['status'], 'num_users' => $r['users_needed']);
    $res = sql_query("SELECT sample_id, tf_id FROM morph_annot_samples WHERE pool_id=$pool_id ORDER BY sample_id");
    $gram_descr = array();
    while ($r = sql_fetch_array($res)) {
        $t = get_context_for_word($r['tf_id'], 4);
        $t['id'] = $r['sample_id'];
        $r1 = sql_fetch_array(sql_query("SELECT COUNT(*) FROM morph_annot_instances WHERE sample_id=".$r['sample_id']." AND answer>0"));
        $t['answered'] = $r1[0];
        if ($extended) {
            $r1 = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id = ".$r['tf_id']." ORDER BY rev_id DESC LIMIT 1"));
            $arr = xml2ary($r1['rev_text']);
            $t['parses'] = get_morph_vars($arr['tfr']['_c']['v'], $gram_descr);
            $res1 = sql_query("SELECT instance_id, answer FROM morph_annot_instances WHERE sample_id=".$r['sample_id']);
            $disagreement_flag = 0;
            $vars = '';
            while ($r1 = sql_fetch_array($res1)) {
                if (!$vars)
                    $vars = $r1['answer'];
                elseif ($vars != $r1['answer'])
                    $disagreement_flag = 1;
                $t['instances'][] = array('id' => $r1['instance_id'], 'answer_num' => $r1['answer'], 'answer_gram' => ($r1['answer'] > 0 && $r1['answer'] < 99) ? $pool_gram[$r1['answer']-1] : '');
            }
            $t['disagreed'] = $disagreement_flag;
        }
        if ($disagreement_flag || !$only_disagreed)
            $out['samples'][] = $t;
    }
    return $out;
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

    $q = "SELECT tf_id, tf_text, pos FROM text_forms WHERE sent_id=(SELECT sent_id FROM text_forms WHERE tf_id=$tf_id LIMIT 1)";
    if ($dir != 0 || $delta > 0) {
        $q_left = $dir <= 0 ? ($delta > 0 ? "(SELECT GREATEST(0, pos-$delta) FROM text_forms WHERE tf_id=$tf_id LIMIT 1)" : "0") : "(SELECT pos FROM text_forms WHERE tf_id=$tf_id LIMIT 1)";
        $q_right = $dir >= 0 ? ($delta > 0 ? "(SELECT pos+$delta FROM text_forms WHERE tf_id=$tf_id LIMIT 1)" : "1000") : "(SELECT pos FROM text_forms WHERE tf_id=$tf_id LIMIT 1)";
        $q .= " AND pos BETWEEN $q_left AND $q_right";
    }

    $q .= " ORDER BY pos";

    $res = sql_query($q);

    while($r = sql_fetch_array($res)) {
        if ($delta > 0) {
            if ($left_c == -1) {
                $left_c = ($r['pos'] == 1) ? 0 : $r['tf_id'];
            }
            if ($mw_pos) {
                if ($r['pos'] >= $mw_pos + $delta)
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
    return array('context' => $t, 'mainword' => $tw, 'has_left_context' => $left_c, 'has_right_context' => $right_c);
}
function add_morph_pool() {
    $pool_name = mysql_real_escape_string(trim($_POST['pool_name']));
    $gr1 = mysql_real_escape_string(str_replace(' ', '', trim($_POST['gram1'])));
    $gr2 = mysql_real_escape_string(str_replace(' ', '', trim($_POST['gram2'])));
    $comment = mysql_real_escape_string(trim($_POST['comment']));
    $gram_descr1 = mysql_real_escape_string(trim($_POST['descr1']));
    $gram_descr2 = mysql_real_escape_string(trim($_POST['descr2']));
    $users = (int)$_POST['users_needed'];
    $timeout = (int)$_POST['timeout'];
    $token_check = (int)$_POST['token_checked'];
    $ts = time();
    sql_begin();
    if (sql_query("INSERT INTO morph_annot_pools VALUES(NULL, '$pool_name', '$gr1@$gr2', '$gram_descr1@$gram_descr2', '$token_check', '$users', '$timeout', '$ts', '$ts', '".$_SESSION['user_id']."', '0', '$comment')")) {
        sql_commit();
        return true;
    }
    return false;
}
function promote_samples($pool_id, $type) {
    if (!$pool_id || !$type) return 0;
    
    $n = isset($_POST['n']) ? (int)$_POST['n'] : 0;
    if (!$n && $type != 'all') return 0;

    $cond = "WHERE pool_id=$pool_id";
    switch($type) {
        case 'first':
            $cond .= " ORDER BY tf_id LIMIT $n";
            break;
        case 'random':
            $cond .= " ORDER BY RAND() LIMIT $n";
    }
    sql_begin();
    if (sql_query("INSERT INTO morph_annot_samples(SELECT NULL, pool_id, tf_id FROM morph_annot_candidate_samples $cond)") &&
        sql_query("UPDATE morph_annot_pools SET `status`='2', `updated_ts`='".time()."' WHERE pool_id=$pool_id LIMIT 1") &&
        sql_query("DELETE FROM morph_annot_candidate_samples WHERE pool_id=$pool_id")) {
        sql_commit();
        return true;
    }
    return false;
}
function publish_pool($pool_id) {
    if (!$pool_id) return 0;

    $r = sql_fetch_array(sql_query("SELECT users_needed FROM morph_annot_pools WHERE pool_id=$pool_id LIMIT 1"));
    $N = $r['users_needed'];
    sql_begin();
    for ($i = 0; $i < $N; ++$i) {
        if (!sql_query("INSERT INTO morph_annot_instances(SELECT NULL, sample_id, 0, 0, 0 FROM morph_annot_samples WHERE pool_id=$pool_id ORDER BY sample_id)")) {
            return false;
        }
    }
    if (sql_query("UPDATE morph_annot_pools SET `status`='3', `updated_ts`='".time()."' WHERE pool_id=$pool_id LIMIT 1")) {
        sql_commit();
        return true;
    }
    return false;
}
function get_available_tasks($user_id) {
    $tasks = array();
    $res = sql_query("SELECT pool_id, pool_name FROM morph_annot_pools WHERE status=3");
    while ($r = sql_fetch_array($res)) {
        $pool = array('id' => $r['pool_id'], 'name' => $r['pool_name']);
        $r1 = sql_fetch_array(sql_query("SELECT COUNT(DISTINCT sample_id) FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=".$r['pool_id'].") AND sample_id NOT IN (SELECT DISTINCT sample_id FROM morph_annot_instances WHERE user_id=$user_id) AND sample_id NOT IN (SELECT sample_id FROM morph_annot_rejected_samples WHERE user_id=$user_id) AND ts_finish=0"));
        $pool['num'] = $r1[0];
        $r1 = sql_fetch_array(sql_query("SELECT COUNT(instance_id) FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=".$r['pool_id'].") AND user_id=$user_id AND answer=0"));
        $pool['num_started'] = $r1[0];
        $tasks[] = $pool;
    }
    return $tasks;
}
function get_annotation_packet($pool_id, $size) {
    $packet = array();
    $r = sql_fetch_array(sql_query("SELECT status, timeout, grammemes, gram_descr FROM morph_annot_pools WHERE pool_id=$pool_id"));
    if ($r['status'] != 3) return false;
    $packet['gram_descr'] = explode('@', $r['gram_descr']);
    $user_id = $_SESSION['user_id'];
    $timeout = $r['timeout'];
    $flag_new = 0;

    //if the user has something already reserved, let's start with that (but only if the poolid is the same!)
    $res = sql_query("SELECT instance_id, sample_id FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND user_id=$user_id AND answer=0 LIMIT $size");
    if (!sql_num_rows($res)) {
        $res = sql_query("SELECT instance_id, sample_id FROM morph_annot_instances WHERE sample_id IN (SELECT sample_id FROM morph_annot_samples WHERE pool_id=$pool_id) AND sample_id NOT IN (SELECT DISTINCT sample_id FROM morph_annot_instances WHERE user_id=$user_id) AND sample_id NOT IN (SELECT sample_id FROM morph_annot_rejected_samples WHERE user_id=$user_id) AND ts_finish=0 AND answer=0 LIMIT $size");
        $flag_new = 1;
    }
    if (!sql_num_rows($res)) return false;

    //when the timeout will be - same for each sample
    $ts_finish = time() + $timeout * sql_num_rows($res);
    if ($flag_new) sql_begin();
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT tf_id FROM morph_annot_samples WHERE sample_id = ".$r['sample_id']." LIMIT 1"));
        $instance = get_context_for_word($r1['tf_id'], 4);
        $instance['id'] = $r['instance_id'];
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
    if (!$id || !$answer) return 0;

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
            !sql_query("UPDATE morph_annot_instances SET user_id='0', ts_finish='0' WHERE instance_id=$id LIMIT 1")
        ) return 0;
    }
    sql_commit();
    return 1;
}
?>
