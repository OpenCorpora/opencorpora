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
?>
