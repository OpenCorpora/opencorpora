<?php
require_once('lib_annot.php');
require_once('lib_dict.php');
require_once('lib_diff.php');
require_once('Lexeme.php');

function main_history($sentence_id, $set_id = 0, $skip = 0, $maa = 0, $user_id = 0) {
    $out = array();
    if (!$sentence_id) {
        if (!$set_id) {
            $q = "SELECT COUNT(DISTINCT tfr.set_id) FROM tf_revisions tfr LEFT JOIN rev_sets s ON (tfr.set_id=s.set_id) ";
            if ($maa)
                $q .= "WHERE (s.comment LIKE '% merged %' or s.comment LIKE '% split %') ";
            if ($user_id) {
                $q .= ($maa ? "AND " : "WHERE ");
                $q .= "user_id = $user_id";
            }
            $res = sql_fetch_array(sql_query($q));
        } else {
            $tf_ids = array(0);
            $res = sql_query("SELECT tf_id FROM tf_revisions WHERE set_id = $set_id".($maa ? " AND set_id IN (SELECT set_id FROM rev_sets WHERE comment LIKE '% merged %' OR comment LIKE '% split %')" : ''));
            while ($r = sql_fetch_array($res))
                $tf_ids[] = $r['tf_id'];
            $res = sql_fetch_array(sql_query("SELECT COUNT(DISTINCT sent_id) FROM tokens WHERE tf_id IN (".join(',', $tf_ids).")"));
        }

        $out['total'] = $res[0];
    }

    if (!$set_id && !$sentence_id) {
        $q = "SELECT DISTINCT tfr.set_id FROM tf_revisions tfr LEFT JOIN rev_sets s ON (tfr.set_id=s.set_id) ";
        if ($maa)
            $q .= "WHERE (s.comment LIKE '% merged %' or s.comment LIKE '% split %') ";
        if ($user_id) {
            $q .= ($maa ? "AND " : "WHERE ");
            $q .= "s.user_id = $user_id ";
        }
        $q .="ORDER BY tfr.set_id DESC LIMIT $skip, 20";

        $res = sql_query($q);
        $res_revset = sql_prepare("SELECT s.comment, s.timestamp, u.user_shown_name AS user_name FROM rev_sets s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE s.set_id=? LIMIT 1");
        while ($r = sql_fetch_array($res)) {
            sql_execute($res_revset, array($r['set_id']));
            $r1 = sql_fetch_array($res_revset);
            $out['sets'][] = array(
                'set_id'    => $r['set_id'],
                'user_name' => $r1['user_name'],
                'timestamp' => $r1['timestamp'],
                'comment'   => $r1['comment']
            );
        }
        $res_revset->closeCursor();
        $res_sent_cnt = sql_prepare("SELECT COUNT(DISTINCT f.sent_id) FROM tf_revisions tfr LEFT JOIN tokens f ON (tfr.tf_id=f.tf_id) WHERE tfr.set_id=?");
        foreach ($out['sets'] as &$set) {
            sql_execute($res_sent_cnt, array($set['set_id']));
            $r2 = sql_fetch_array($res_sent_cnt);
            $set['sent_cnt'] = $r2[0];
        }
        $res_sent_cnt->closeCursor();
    } else {
        $res = sql_query("SELECT tr.set_id, st.sent_id, s.timestamp, s.comment, u.user_shown_name AS user_name FROM tf_revisions tr LEFT JOIN rev_sets s ON (tr.set_id=s.set_id) LEFT JOIN users u ON (s.user_id=u.user_id) RIGHT JOIN tokens tf ON (tr.tf_id = tf.tf_id) RIGHT JOIN sentences st ON (tf.sent_id = st.sent_id) ".($maa ? "WHERE tr.set_id IN (SELECT set_id FROM rev_sets WHERE comment LIKE '% merged %' OR comment LIKE '% split %') AND " : 'WHERE ').($set_id?"tr.set_id=$set_id GROUP BY st.sent_id":"st.sent_id=$sentence_id GROUP BY tr.set_id")." ORDER BY tr.rev_id DESC LIMIT $skip,20");
        while ($r = sql_fetch_array($res)) {
            $out['sets'][] = array(
                'set_id'       => $r['set_id'],
                'user_name'    => $r['user_name'],
                'timestamp'    => $r['timestamp'],
                'sent_cnt'     => isset($r['cnt']) ? $r['cnt'] : 0,
                'sent_id'      => isset($r['sent_id']) ? $r['sent_id'] : 0,
                'comment_html' => history_get_comment_html($r['comment']),
                'comment'      => $r['comment']
            );
        }
    }

    return $out;
}
function history_get_comment_html($comment) {
    $pattern = array('/Merge data from annotation pool #(\d+)/i');
    $replacement = array('Merge data from annotation pool <a href="/pools.php?act=samples&amp;pool_id=$1">#$1</a>');
    return preg_replace ($pattern, $replacement, $comment);
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
                        (SELECT s.*, u.user_shown_name AS user_name, dl.*,
                         '0' AS lemma2_id, '0' AS lemma2_text, '0' AS is_link, users_ugc.user_shown_name AS ugc_user_name
                            FROM dict_revisions dr
                            LEFT JOIN rev_sets s ON (dr.set_id=s.set_id)
                            LEFT JOIN users u ON (s.user_id=u.user_id)
                            LEFT JOIN dict_lemmata dl ON (dr.lemma_id=dl.lemma_id)
                            LEFT JOIN dict_revisions_ugc ugc
                                ON (dr.ugc_rev_id = ugc.rev_id)
                            LEFT JOIN users users_ugc
                                ON (ugc.user_id = users_ugc.user_id)
                            ".($lemma_id?" WHERE dr.lemma_id=$lemma_id":"")."
                            ORDER BY dr.rev_id DESC LIMIT ".($skip+20).")
                        UNION
                        (SELECT s.*, u.user_shown_name AS user_name, dl.*,
                         dl2.lemma_id AS lemma2_id, dl2.lemma_text AS lemma2_text, '1' AS is_link, '' AS ugc_user_name
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
    while ($r = sql_fetch_array($res)) {
        $out['sets'][] = array (
            'set_id'     => $r['set_id'],
            'user_name'  => $r['user_name'],
            'timestamp'  => $r['timestamp'],
            'comment'    => $r['comment'],
            'lemma_id'   => $r['lemma_id'],
            'lemma_text' => $r['lemma_text'],
            'lemma2_id'   => $r['lemma2_id'],
            'lemma2_text' => $r['lemma2_text'],
            'is_link'    => $r['is_link'],
            'is_lemma_deleted' => $r['deleted'],
            'ugc_user_name' => $r['ugc_user_name']
        );
    }
    return $out;
}
function main_diff($sentence_id, $set_id, $rev_id) {
    if (!$sentence_id || !$set_id) {
        if (!$rev_id)
            throw new UnexpectedValueException();
        $r = sql_fetch_array(sql_query("
            SELECT sent_id, set_id
            FROM tf_revisions
            LEFT JOIN tokens USING (tf_id)
            WHERE rev_id = $rev_id
            LIMIT 1
        "));

        $sentence_id = $r['sent_id'];
        $set_id = $r['set_id'];
    }
    $r = sql_fetch_array(sql_query("SELECT DISTINCT s.*, u.user_shown_name AS user_name FROM rev_sets s LEFT JOIN `users` u ON (s.user_id = u.user_id) WHERE s.set_id=$set_id"));
    $out = array(
        'set_id'    => $set_id,
        'sent_id'   => $sentence_id,
        'user_name' => $r['user_name'],
        'timestamp' => $r['timestamp'],
        'comment'   => $r['comment'],
        'merge_pool_id' => (preg_match('/ pool #(\d+)$/', $r['comment'], $ms) ? $ms[1] : false),
        'prev_set'  => 0,
        'next_set'  => 0,
        'tokens'    => array()
    );
    $res = sql_query("SELECT tf_id, `pos` FROM tokens WHERE sent_id=$sentence_id ORDER BY `pos`");
    $token_ids = array();
    $res_rev = sql_prepare("SELECT tr.*, rs.*, `users`.user_shown_name AS user_name FROM tf_revisions tr LEFT JOIN rev_sets rs ON (tr.set_id = rs.set_id) LEFT JOIN `users` ON (rs.user_id = `users`.user_id) WHERE tr.tf_id=? AND tr.set_id<=? ORDER BY tr.rev_id DESC LIMIT 2");
    while ($r = sql_fetch_array($res)) {
        $token_ids[] = $r['tf_id'];
        $token = array();
        sql_execute($res_rev, array($r['tf_id'], $set_id));
        $r1 = sql_fetch_array($res_rev);
        $r2 = sql_fetch_array($res_rev);
        if ($r1['set_id'] != $set_id)
            continue;
        $old_rev = format_xml($r2['rev_text']);
        $new_rev = format_xml($r1['rev_text']);
        $token = array(
            'pos'           => $r['pos'],
            'old_ver'       => $r2['rev_id'],
            'new_ver'       => $r1['rev_id'],
            'old_user_name' => $r2['user_name'],
            'new_user_name' => $r1['user_name'],
            'old_timestamp' => $r2['timestamp'],
            'new_timestamp' => $r1['timestamp'],
            'new_rev_xml'   => $new_rev,
            'diff'          => php_diff($old_rev, $new_rev)
        );
        $out['tokens'][] = $token;
    }
    $res_rev->closeCursor();

    // previous set
    $res = sql_query("SELECT set_id FROM tf_revisions WHERE tf_id IN (".join(',', $token_ids).") AND set_id<$set_id ORDER BY set_id DESC LIMIT 1");
    $r = sql_fetch_array($res);
    if ($r)
        $out['prev_set'] = $r[0];

    // next set
    $res = sql_query("SELECT set_id FROM tf_revisions WHERE tf_id IN (".join(',', $token_ids).") AND set_id>$set_id ORDER BY set_id ASC LIMIT 1");
    $r = sql_fetch_array($res);
    if ($r)
        $out['next_set'] = $r[0];

    return $out;
}
function dict_diff($lemma_id, $set_id) {
    $res = sql_pe("
        SELECT dr.rev_id, dr.rev_text, s.timestamp, s.comment, u.user_shown_name AS user_name
        FROM dict_revisions dr
        LEFT JOIN rev_sets s ON (dr.set_id=s.set_id)
        LEFT JOIN `users` u ON (s.user_id=u.user_id)
        WHERE dr.set_id <= ?
            AND dr.lemma_id = ?
        ORDER BY dr.rev_id DESC
        LIMIT 2
    ", array($set_id, $lemma_id));
    $r1 = $res[0];
    $r2 = $res[1];
    $old_rev = format_xml($r2['rev_text']);
    $new_rev = format_xml($r1['rev_text']);
    $out = array(
        'lemma_id'      => $lemma_id,
        'old_ver'       => $r2['rev_id'],
        'new_ver'       => $r1['rev_id'],
        'old_user_name' => $r2['user_name'],
        'new_user_name' => $r1['user_name'],
        'old_timestamp' => $r2['timestamp'],
        'new_timestamp' => $r1['timestamp'],
        'comment'       => $r1['comment'],
        'new_rev_xml'   => $new_rev,
        'diff'          => php_diff($old_rev, $new_rev),
        'prev_set'      => 0,
        'next_set'      => 0
    );
    $res = sql_pe("
        SELECT set_id
        FROM dict_revisions
        WHERE lemma_id = ?
            AND set_id < ?
        ORDER BY set_id DESC
        LIMIT 1
    ", array($lemma_id, $set_id));
    if (sizeof($res) > 0) {
        $out['prev_set'] = $res[0]['set_id'];
    }
    $res = sql_pe("
        SELECT set_id
        FROM dict_revisions
        WHERE lemma_id = ?
            AND set_id > ?
        ORDER BY set_id ASC
        LIMIT 1
    ", array($lemma_id, $set_id));
    if (sizeof($res) > 0) {
        $out['next_set'] = $res[0]['set_id'];
    }
    return $out;
}
function revert_changeset($set_id, $comment) {
    if (!$set_id)
        throw new UnexpectedValueException();
    check_permission(PERM_DICT);

    sql_begin();
    $new_set_id = current_revset($comment);
    $dict_flag = 0;

    $res = sql_pe("SELECT tf_id FROM tf_revisions WHERE set_id=?", array($set_id));
    $res_revtext = sql_prepare("SELECT rev_text FROM tf_revisions WHERE tf_id=? AND set_id<? ORDER BY rev_id DESC LIMIT 1");
    $new_revisions = [];
    foreach ($res as $r) {
        sql_execute($res_revtext, array($r[0], $set_id));
        $arr = sql_fetch_array($res_revtext);
        $new_revisions[] = [$r[0], $arr[0]];
    }
    $res_revtext->closeCursor();
    foreach ($new_revisions as $rev) {
        create_tf_revision($new_set_id, $rev[0], $rev[1]);
    }

    $res = sql_pe("SELECT lemma_id FROM dict_revisions WHERE set_id=?", array($set_id));
    $res_revtext = sql_prepare("SELECT rev_text FROM dict_revisions WHERE lemma_id=? AND set_id<? ORDER BY rev_id DESC LIMIT 1");
    foreach ($res as $r) {
        sql_execute($res_revtext, array($r[0], $set_id));
        $arr = sql_fetch_array($res_revtext);
        new_dict_rev($r[0], $arr[0]);
        $dict_flag = 1;
    }
    $res_revtext->closeCursor();
    sql_commit();

    if ($dict_flag)
        return 'dict_history.php';
    return 'history.php';
}
function revert_token($rev_id) {
    if (!$rev_id)
        throw new UnexpectedValueException();

    $res = sql_pe("SELECT tf_id, rev_text FROM tf_revisions WHERE rev_id=? LIMIT 1", array($rev_id));
    sql_begin();
    $new_set_id = current_revset("Отмена правки, возврат к версии t$rev_id");

    create_tf_revision($new_set_id, $res[0]['tf_id'], $res[0]['rev_text']);
    sql_commit();
}
function revert_dict($rev_id) {
    if (!$rev_id)
        throw new UnexpectedValueException();
    check_permission(PERM_DICT);

    $res = sql_pe("SELECT lemma_id, rev_text FROM dict_revisions WHERE rev_id=? LIMIT 1", array($rev_id));
    $lemma_id = $res[0]['lemma_id'];
    $old_rev = sql_pe("SELECT rev_text FROM dict_revisions WHERE lemma_id=? and is_last=1 LIMIT 1", array($lemma_id));

    sql_begin();
    $new_set_id = current_revset("Отмена правки, возврат к версии d$rev_id");
    $new_rev_id = new_dict_rev($lemma_id, $res[0]['rev_text']);

    // updated forms
    $old_lex = new Lexeme($old_rev[0]['rev_text']);
    $new_lex = new Lexeme($res[0]['rev_text']);
    enqueue_updated_forms(calculate_updated_forms($old_lex, $new_lex), $new_rev_id);

    sql_commit();
}
function get_latest_comments($skip = 0) {
    $out = array();

    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt FROM sentence_comments"));
    $out['total'] = $r['cnt'];

    $res = sql_query("SELECT sc.comment_id, sc.sent_id, u.user_shown_name AS user_name, sc.timestamp, SUBSTRING_INDEX(sc.text, ' ', 8) txt FROM sentence_comments sc LEFT JOIN users u ON (sc.user_id=u.user_id) ORDER BY comment_id DESC LIMIT $skip,20");

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
