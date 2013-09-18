<?php
function get_syntax_group_types() {
    $res = sql_query_pdo("SELECT type_id, type_name FROM syntax_group_types ORDER BY type_name");
    $out = array();
    while ($r = sql_fetch_array($res))
        $out[$r['type_id']] = $r['type_name'];
    return $out;
}
function get_groups_by_sentence($sent_id) {
    $out = array(
        'simple' => array(),
        'complex' => array()
    );

    $res = sql_query_pdo("
        SELECT group_id, type_name, token_id
        FROM syntax_groups_simple sg
        JOIN syntax_groups g USING (group_id)
        JOIN syntax_group_types gt ON (g.group_type = gt.type_id)
        JOIN text_forms tf ON (sg.token_id = tf.tf_id)
        JOIN sentences s USING (sent_id)
        WHERE sent_id = $sent_id
        ORDER BY group_id, token_id
    ");

    $simple = array();
    $last_r = NULL;
    $token_ids = array();

    while ($r = sql_fetch_array($res)) {
        if ($last_r && $r['group_id'] != $last_r['group_id']) {
            $out['simple'][] = array(
                'id' => $last_r['group_id'],
                'type' => $last_r['type_name'],
                'tokens' => $token_ids
            );
            $token_ids = array();
            $simple[] = $r['group_id'];
        }
        $token_ids[] = $r['token_id'];
        $last_r = $r;
    }
}
?>
