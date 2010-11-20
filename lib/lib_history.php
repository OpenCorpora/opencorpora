<?php
function main_history($sentence_id) {
    $out = array();
    $res = sql_query("SELECT DISTINCT s.*, u.user_name, st.sent_id FROM rev_sets s LEFT JOIN `users` u ON (s.user_id = u.user_id) LEFT JOIN `tf_revisions` tr ON (s.set_id = tr.set_id) RIGHT JOIN `text_forms` tf ON (tr.tf_id = tf.tf_id) RIGHT JOIN `sentences` st ON (tf.sent_id = st.sent_id)".($sentence_id?" WHERE st.sent_id=$sentence_id":"")." ORDER BY s.set_id DESC, tr.rev_id LIMIT 20");
    while($r = sql_fetch_array($res)) {
        $out[] = array (
            'set_id'    => $r['set_id'],
            'user_name' => $r['user_name'],
            'timestamp' => $r['timestamp'],
            'sent_id'   => $r['sent_id']
        );
    }
    return $out;
}
function dict_history($lemma_id) {
    $out = array();
    $res = sql_query("SELECT s.*, u.user_name, dl.* FROM dict_revisions dr LEFT JOIN rev_sets s ON (dr.set_id=s.set_id) LEFT JOIN users u ON (s.user_id=u.user_id) LEFT JOIN dict_lemmata dl ON (dr.lemma_id=dl.lemma_id)".($lemma_id?" WHERE dr.lemma_id=$lemma_id":"")." ORDER BY dr.rev_id DESC LIMIT 20");
    while($r = sql_fetch_array($res)) {
        $out[] = array (
            'set_id'     => $r['set_id'],
            'user_name'  => $r['user_name'],
            'timestamp'  => $r['timestamp'],
            'lemma_id'   => $r['lemma_id'],
            'lemma_text' => $r['lemma_text']
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
    return $out;
}
function dict_diff($lemma_id, $set_id) {
    $res = sql_query("SELECT dr.rev_id, dr.rev_text, s.timestamp, u.user_name FROM dict_revisions dr LEFT JOIN rev_sets s ON (dr.set_id=s.set_id) LEFT JOIN `users` u ON (s.user_id=u.user_id) WHERE dr.set_id<=$set_id AND dr.lemma_id=$lemma_id ORDER BY dr.rev_id DESC LIMIT 2");
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
        'old_rev_xml'   => $r2['rev_text'],
        'new_rev_xml'   => $r1['rev_text']
    );
    return $out;
}
?>
