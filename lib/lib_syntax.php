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
function add_simple_group($token_ids, $type) {
    $token_ids = array_map('intval', $token_ids);
    $res = sql_query_pdo("
        SELECT DISTINCT sent_id
        FROM text_forms
        WHERE tf_id IN (".join(',', $token_ids).")
    ");
    
    if (sql_num_rows($res) > 1 || !$type)
        return false;

    sql_begin();

    $revset_id = create_revset();
    if (!$revset_id)
        return false;

    if (!sql_query("INSERT INTO syntax_groups VALUES (NULL, $type, $revset_id, 0)"))
        return false;
    $group_id = sql_insert_id();

    foreach ($token_ids as $token_id)
        if (!sql_query("INSERT INTO syntax_groups_simple VALUES ($group_id, $token_id)"))
            return false;

    sql_commit();
    return $group_id;
}
function set_group_head($group_id, $head_id) {
    // assume the group is simple for now

    // check if head belongs to the group
    $res = sql_query_pdo("SELECT * FROM syntax_groups_simple WHERE group_id=$group_id AND token_id=$head_id LIMIT 1");
    if (!sql_num_rows($res))
        return false;

    // set the head
    if (sql_query("UPDATE syntax_groups SET head_id=$head_id WHERE group_id=$group_id LIMIT 1"))
        return true;
    return false;
}
?>
