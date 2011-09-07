<?php
function main_history($sentence_id, $set_id = 0, $skip = 0) {
    $out = array();
    if (!$sentence_id) {
        if (!$set_id)
            $res = sql_fetch_array(sql_query("SELECT COUNT(DISTINCT set_id) FROM tf_revisions"));
        else
            $res = sql_fetch_array(sql_query("SELECT COUNT(DISTINCT sent_id) FROM text_forms WHERE tf_id IN (SELECT tf_id FROM tf_revisions WHERE set_id = $set_id)"));

        $out['total'] = $res[0];
    }

    if (!$set_id && !$sentence_id)
        $res = sql_query("SELECT set_id, COUNT(sent_id) cnt FROM (SELECT set_id, f.sent_id FROM tf_revisions r RIGHT JOIN text_forms f ON (r.tf_id=f.tf_id) RIGHT JOIN sentences s ON (f.sent_id=s.sent_id)".($sentence_id?" WHERE s.sent_id=$sentence_id":"")." GROUP BY set_id, f.sent_id ORDER BY set_id DESC) T GROUP BY set_id ORDER BY set_id DESC LIMIT $skip,20");
    else
        $res = sql_query("SELECT tr.set_id, st.sent_id FROM tf_revisions tr RIGHT JOIN text_forms tf ON (tr.tf_id = tf.tf_id) RIGHT JOIN sentences st ON (tf.sent_id = st.sent_id) WHERE ".($set_id?"tr.set_id=$set_id GROUP BY st.sent_id":"st.sent_id=$sentence_id GROUP BY tr.set_id")." ORDER BY tr.rev_id DESC LIMIT $skip,20");
    while($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT u.user_name, s.timestamp, s.comment FROM rev_sets s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE s.set_id = ".$r['set_id']." LIMIT 1"));
        $out['sets'][] = array(
            'set_id'    => $r['set_id'],
            'user_name' => $r1['user_name'],
            'timestamp' => $r1['timestamp'],
            'sent_cnt'  => $r['cnt'],
            'sent_id'   => $r['sent_id'],
            'comment'   => $r1['comment']
        );
    }

    return $out;
}
function dict_history($lemma_id, $skip = 0) {
    $out = array();
    if (!$lemma_id) {
        $res = sql_fetch_array(sql_query("SELECT COUNT(*) FROM dict_revisions"));
        $out['total'] = $res[0];
        $res = sql_fetch_array(sql_query("SELECT COUNT(*) FROM dict_links_revisions"));
        $out['total'] += $res[0];
    }
    $res = sql_query("SELECT * FROM (
                        (SELECT s.*, u.user_name, dl.*, '0' lemma2_id, '0' lemma2_text, '0' is_link
                            FROM dict_revisions dr
                            LEFT JOIN rev_sets s ON (dr.set_id=s.set_id)
                            LEFT JOIN users u ON (s.user_id=u.user_id)
                            LEFT JOIN dict_lemmata dl ON (dr.lemma_id=dl.lemma_id)
                            ".($lemma_id?" WHERE dr.lemma_id=$lemma_id":"")." 
                            ORDER BY dr.rev_id DESC LIMIT ".($skip+20).")
                        UNION
                        (SELECT s.*, u.user_name, dl.*, dl2.lemma_id lemma2_id, dl2.lemma_text lemma2_text, '1' is_link
                            FROM dict_links_revisions dr
                            LEFT JOIN rev_sets s ON (dr.set_id=s.set_id)
                            LEFT JOIN users u ON (s.user_id=u.user_id)
                            LEFT JOIN dict_lemmata dl ON (dr.lemma1_id=dl.lemma_id)
                            LEFT JOIN dict_lemmata dl2 ON (dr.lemma2_id=dl2.lemma_id)
                            ".($lemma_id?" WHERE dr.lemma1_id=$lemma_id OR dr.lemma2_id=$lemma_id":"")."
                            ORDER BY dr.rev_id DESC LIMIT ".($skip+20).")
                        ) T
                        ORDER BY set_id DESC, lemma_id DESC LIMIT $skip,20
                    ");
    while($r = sql_fetch_array($res)) {
        $out['sets'][] = array (
            'set_id'     => $r['set_id'],
            'user_name'  => $r['user_name'],
            'timestamp'  => $r['timestamp'],
            'comment'    => $r['comment'],
            'lemma_id'   => $r['lemma_id'],
            'lemma_text' => $r['lemma_text'],
            'lemma2_id'   => $r['lemma2_id'],
            'lemma2_text' => $r['lemma2_text'],
            'is_link'    => $r['is_link']
        );
    }
    return $out;
}
function main_diff($sentence_id, $set_id) {
    $r = sql_fetch_array(sql_query("SELECT DISTINCT s.*, u.user_name FROM rev_sets s LEFT JOIN `users` u ON (s.user_id = u.user_id) WHERE s.set_id=$set_id"));
    $out = array(
        'set_id'    => $set_id,
        'sent_id'   => $sentence_id,
        'user_name' => $r['user_name'],
        'timestamp' => $r['timestamp'],
        'comment'   => $r['comment'],
        'prev_set'  => 0,
        'next_set'  => 0,
        'tokens'    => array()
    );
    $res = sql_query("SELECT tf_id, `pos` FROM text_forms WHERE sent_id=$sentence_id ORDER BY `pos`");
    while($r = sql_fetch_array($res)) {
        $token = array();
        $res1 = sql_query("SELECT tr.*, rs.*, `users`.user_name FROM tf_revisions tr LEFT JOIN rev_sets rs ON (tr.set_id = rs.set_id) LEFT JOIN `users` ON (rs.user_id = `users`.user_id) WHERE tr.tf_id=".$r['tf_id']." AND tr.set_id<=$set_id ORDER BY tr.rev_id DESC LIMIT 2");
        $r1 = sql_fetch_array($res1);
        $r2 = sql_fetch_array($res1);
        if ($r1['set_id'] != $set_id)
            continue;
        $token = array(
            'pos'           => $r['pos'],
            'old_ver'       => $r2['rev_id'],
            'new_ver'       => $r1['rev_id'],
            'old_user_name' => $r2['user_name'],
            'new_user_name' => $r1['user_name'],
            'old_timestamp' => $r2['timestamp'],
            'new_timestamp' => $r1['timestamp'],
            'old_rev_xml'   => $r2['rev_text'],
            'new_rev_xml'   => $r1['rev_text']
        );
        $out['tokens'][] = $token;
    }
    $res = sql_query("SELECT set_id FROM tf_revisions WHERE tf_id IN (SELECT tf_id FROM text_forms WHERE sent_id=$sentence_id) AND set_id<$set_id ORDER BY set_id DESC LIMIT 1");
    if ($res) {
        $r = sql_fetch_array($res);
        $out['prev_set'] = $r[0];
    }
    $res = sql_query("SELECT set_id FROM tf_revisions WHERE tf_id IN (SELECT tf_id FROM text_forms WHERE sent_id=$sentence_id) AND set_id>$set_id ORDER BY set_id ASC LIMIT 1");
    if ($res) {
        $r = sql_fetch_array($res);
        $out['next_set'] = $r[0];
    }
    return $out;
}
function dict_diff($lemma_id, $set_id) {
    $res = sql_query("SELECT dr.rev_id, dr.rev_text, s.timestamp, s.comment, u.user_name FROM dict_revisions dr LEFT JOIN rev_sets s ON (dr.set_id=s.set_id) LEFT JOIN `users` u ON (s.user_id=u.user_id) WHERE dr.set_id<=$set_id AND dr.lemma_id=$lemma_id ORDER BY dr.rev_id DESC LIMIT 2");
    $r1 = sql_fetch_array($res);
    $r2 = sql_fetch_array($res);
    $out = array(
        'lemma_id'      => $lemma_id,
        'old_ver'       => $r2['rev_id'],
        'new_ver'       => $r1['rev_id'],
        'old_user_name' => $r2['user_name'],
        'new_user_name' => $r1['user_name'],
        'old_timestamp' => $r2['timestamp'],
        'new_timestamp' => $r1['timestamp'],
        'comment'       => $r1['comment'],
        'old_rev_xml'   => $r2['rev_text'],
        'new_rev_xml'   => $r1['rev_text'],
        'prev_set'      => 0,
        'next_set'      => 0
    );
    $res = sql_query("SELECT set_id FROM dict_revisions WHERE lemma_id=$lemma_id AND set_id<$set_id ORDER BY set_id DESC LIMIT 1");
    if ($res) {
        $r = sql_fetch_array($res);
        $out['prev_set'] = $r[0];
    }
    $res = sql_query("SELECT set_id FROM dict_revisions WHERE lemma_id=$lemma_id AND set_id>$set_id ORDER BY set_id ASC LIMIT 1");
    if ($res) {
        $r = sql_fetch_array($res);
        $out['next_set'] = $r[0];
    }
    return $out;
}
function revert_changeset($set_id, $comment) {
    if (!$set_id) return;

    sql_begin();
    $new_set_id = create_revset($comment);
    $dict_flag = 0;

    $res = sql_query("SELECT tf_id FROM tf_revisions WHERE set_id=$set_id");
    while ($r = sql_fetch_array($res)) {
        $arr = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=$r[0] AND set_id<$set_id ORDER BY rev_id DESC LIMIT 1"));
        if (!sql_query("INSERT INTO `tf_revisions` VALUES(NULL, '$new_set_id', '$r[0]', '$arr[0]')")) {
            show_error();
            return;
        }
    }

    $res = sql_query("SELECT lemma_id FROM dict_revisions WHERE set_id=$set_id");
    while ($r = sql_fetch_array($res)) {
        $arr = sql_fetch_array(sql_query("SELECT rev_text FROM dict_revisions WHERE lemma_id=$r[0] AND set_id<$set_id ORDER BY rev_id DESC LIMIT 1"));
        if (!sql_query("INSERT INTO `dict_revisions` VALUES(NULL, '$new_set_id', '$r[0]', '$arr[0]')")) {
            show_error();
            return;
        }
        $dict_flag = 1;
    }
    sql_commit();

    if ($dict_flag)
        header("Location:dict_history.php");
    else
        header("Location:history.php");
    return;
}
function revert_token($rev_id) {
    if (!$rev_id) return;

    $r = sql_fetch_array(sql_query("SELECT tf_id, rev_text FROM tf_revisions WHERE rev_id=$rev_id LIMIT 1"));
    sql_begin();
    $new_set_id = create_revset("Отмена правки, возврат к версии t$rev_id");

    if (sql_query("INSERT INTO tf_revisions VALUES(NULL, '$new_set_id', '$r[0]', '$r[1]')")) {
        sql_commit();
        header("Location:history.php");
    } else {
        show_error();
    }
    return;
}
function revert_dict($rev_id) {
    if (!$rev_id) return;

    $r = sql_fetch_array(sql_query("SELECT lemma_id, rev_text FROM dict_revisions WHERE rev_id=$rev_id LIMIT 1"));
    sql_begin();
    $new_set_id = create_revset("Отмена правки, возврат к версии d$rev_id");

    if (sql_query("INSERT INTO dict_revisions VALUES(NULL, '$new_set_id', '$r[0]', '$r[1]', '0', '0')")) {
        sql_commit();
        header("Location:dict_history.php");
    } else {
        show_error();
    }
    return;
}
function get_latest_comments($skip = 0) {
    $out = array();

    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt FROM sentence_comments"));
    $out['total'] = $r['cnt'];

    $res = sql_query("SELECT sc.comment_id, sc.sent_id, u.user_name, sc.timestamp, SUBSTRING_INDEX(sc.text, ' ', 8) txt FROM sentence_comments sc LEFT JOIN users u ON (sc.user_id=u.user_id) ORDER BY comment_id DESC LIMIT $skip,20");

    while ($r = sql_fetch_array($res)) {
        $out['c'][] = array(
            'id' => $r['comment_id'],
            'sent_id' => $r['sent_id'],
            'user_name' => $r['user_name'],
            'ts' => $r['timestamp'],
            'text' => $r['txt']
        );
    }

    return $out;
}
?>
