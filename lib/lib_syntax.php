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
function add_simple_group($token_ids, $type, $revset_id=0) {
    $token_ids = array_map('intval', $token_ids);
    $res = sql_query_pdo("
        SELECT DISTINCT sent_id
        FROM text_forms
        WHERE tf_id IN (".join(',', $token_ids).")
    ");
    
    if (sql_num_rows($res) > 1)
        return false;

    sql_begin();

    if (!$revset_id)
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
function parse_complex_group_data() {
    // input: whatever structure comes from frontend
    // output: array of array(2): [int id, bool is_group_id (otherwise token_id)]
}
function add_complex_group($ids, $type) {
    // assume input has gone through parse_complex_group_data()

    // TODO recursively check that everything is within one sentence
    sql_begin();

    $revset_id = create_revset();
    if (!$revset_id)
        return false;

    if (!sql_query("INSERT INTO syntax_groups VALUES (NULL, $type, $revset_id, 0)"))
        return false;
    $group_id = sql_insert_id();

    foreach ($ids as $id) {
        $cur_id = ($id[1] == true) ? $id[0] : get_dummy_group_for_token($id[0]);
        if (!$cur_id)
            return false;
        if (!sql_query("INSERT INTO syntax_groups_complex VALUES ($group_id, $cur_id)"))
            return false;
    }
    sql_commit();
    return $group_id;
}
function add_dummy_group($token_id, $revset_id=0) {
    sql_begin();
    if (!$revset_id)
        $revset_id = create_revset();
    $gid = add_simple_group(array($token_id), 16, $revset_id);
    if (!$gid)
        return false;
    sql_commit();
    return $gid;
}
function get_dummy_group_for_token($token_id, $create_if_absent=true) {
    $res = sql_query_pdo("SELECT group_id FROM syntax_groups_simple WHERE group_type=16 AND token_id=$token_id");
    if (sql_num_rows($res) > 1)
        return false;
    if (sql_num_rows($res) == 1) {
        $r = sql_fetch_array($res);
        return $r['group_id'];
    }

    // therefore there is none
    if ($create_if_absent)
        return add_dummy_group($token_id);
    else
        return false;
}
function delete_group($group_id) {
    sql_begin();
    if (
        !sql_query("DELETE FROM syntax_groups_simple WHERE group_id=$group_id") ||
        !sql_query("DELETE FROM syntax_groups_complex WHERE group_id=$group_id") ||
        !sql_query("DELETE FROM syntax_groups WHERE group_id=$group_id LIMIT 1")
    )
        return false;
    sql_commit();
    return true;
}
function set_group_head($group_id, $head_id) {
    // assume that the head of a complex group is also a group

    // check if head belongs to the group
    $res = sql_query_pdo("SELECT * FROM syntax_groups_simple WHERE group_id=$group_id AND token_id=$head_id LIMIT 1");
    if (!sql_num_rows($res)) {
        // perhaps the group is complex then
        $res = sql_query_pdo("SELECT * FROM syntax_groups_complex WHERE parent_gid=$group_id AND child_gid=$head_id");
        if (!sql_num_rows($res))
            return false;
    }

    // set the head
    if (sql_query("UPDATE syntax_groups SET head_id=$head_id WHERE group_id=$group_id LIMIT 1"))
        return true;
    return false;
}
?>
