<?php
function get_books_with_syntax() {
    $res = sql_query_pdo("SELECT book_id, status, user_id FROM syntax_annotators");
    $syntax = array();
    while ($r = sql_fetch_array($res)) {
        if (!isset($syntax[$r['book_id']]))
            $syntax[$r['book_id']] = array(1 => 0, 2 => 0);
        if ($r['user_id'] == $_SESSION['user_id'])
            $syntax[$r['book_id']]['self'] = $r['status'];
        $syntax[$r['book_id']][$r['status']] += 1;
    }

    $res = sql_query_pdo("SELECT book_id, book_name FROM books WHERE syntax_on=1 ORDER BY book_id");
    $out = array();
    while ($r = sql_fetch_array($res))
        $out[] = array(
            'id' => $r['book_id'],
            'name' => $r['book_name'],
            'status' => array(
                'syntax' => array(
                    'self' => isset($syntax[$r['book_id']]['self']) ? $syntax[$r['book_id']]['self'] : 0,
                    'total' => isset($syntax[$r['book_id']]) ? $syntax[$r['book_id']] : array(1 => 0, 2 => 0)
                ),
                'anaphor' => 0
            )
        );
    return $out;
}
function get_syntax_group_types() {
    $res = sql_query_pdo("SELECT type_id, type_name FROM syntax_group_types ORDER BY type_name");
    $out = array();
    while ($r = sql_fetch_array($res))
        $out[$r['type_id']] = $r['type_name'];
    return $out;
}
function group_type_exists($type) {
    if ($type == 0)
        return true;
    $res = sql_query_pdo("SELECT type_id FROM syntax_group_types WHERE type_id=$type LIMIT 1");
    return sql_num_rows($res) > 0;
}
function get_simple_groups_by_sentence($sent_id, $user_id) {
    $out = array();
    $res = sql_query_pdo("
        SELECT group_id, group_type, token_id, tf_text, head_id, tf.pos
        FROM syntax_groups_simple sg
        JOIN syntax_groups g USING (group_id)
        JOIN text_forms tf ON (sg.token_id = tf.tf_id)
        WHERE sent_id = $sent_id
        AND user_id = $user_id
        ORDER BY group_id, tf.pos
    ");

    $last_r = NULL;
    $token_ids = array();
    $token_texts = array();
    $token_pos = array();

    while ($r = sql_fetch_array($res)) {
        if ($last_r && $r['group_id'] != $last_r['group_id']) {
            $out[] = array(
                'id' => $last_r['group_id'],
                'type' => $last_r['group_type'],
                'tokens' => $token_ids,
                'token_texts' => $token_texts,
                'head_id' => $last_r['head_id'],
                'text' => join(' ', array_values($token_texts)),
                'start_pos' => min($token_pos),
                'end_pos' => max($token_pos)
            );
            $token_ids = $token_texts = $token_pos = array();
        }
        $token_ids[] = $r['token_id'];
        $token_pos[] = $r['pos'];
        $token_texts[$r['token_id']] = $r['tf_text'];
        $last_r = $r;
    }
    if (sizeof($token_ids) > 0) {
        $out[] = array(
            'id' => $last_r['group_id'],
            'type' => $last_r['group_type'],
            'tokens' => $token_ids,
            'token_texts' => $token_texts,
            'head_id' => $last_r['head_id'],
            'text' => join(' ', array_values($token_texts)),
            'start_pos' => min($token_pos)
        );
    }

    return $out;
}
function get_complex_groups_by_simple($simple_groups, $user_id) {
    $groups = array();
    $possible_children = array();
    $groups_pos = array();
    $groups_text = array();
    foreach ($simple_groups as $g) {
        $possible_children[] = $g['id'];
        $groups_pos[$g['id']] = $g['start_pos'];
        $groups_text[$g['id']] = $g['text'];
    }
    $new_added = true;
    while ($new_added) {
        $new_added = false;
        $res = sql_query_pdo("
            SELECT parent_gid, child_gid, group_type, head_id
            FROM syntax_groups g
            JOIN syntax_groups_complex gc
                ON (g.group_id = gc.parent_gid)
            WHERE user_id = $user_id
                AND child_gid IN (".join(',', $possible_children).")
            ORDER BY parent_gid
        ");
        
        while ($r = sql_fetch_array($res)) {
            if (isset($groups[$r['parent_gid']])) {
                $groups[$r['parent_gid']]['children'] = array_unique(array_merge($groups[$r['parent_gid']]['children'], array($r['child_gid'])));
                $groups[$r['parent_gid']]['start_pos'] = min($groups[$r['parent_gid']]['start_pos'], $groups_pos[$r['child_gid']]);
            }
            else {
                // new group
                $new_added = true;
                $possible_children[] = $r['parent_gid'];
                $groups[$r['parent_gid']] = array(
                    'type' => $r['group_type'],
                    'children' => array($r['child_gid']),
                    'head_id' => $r['head_id'],
                    'start_pos' => $groups_pos[$r['child_gid']]
                );
            }
            $groups_pos[$r['parent_gid']] = $groups[$r['parent_gid']]['start_pos'];
        }
    }

    $out = array();
    ksort($groups);
    foreach ($groups as $id => $g) {
        $atext = array();
        foreach ($g['children'] as $ch)
            $atext[$ch] = array($groups_pos[$ch], $groups_text[$ch]);
        uasort($atext, function($a, $b) {
            if ($a[0] < $b[0])
                return -1;
            if ($a[0] > $b[0])
                return 1;
            return 0;
        });
        $groups_text[$id] = join(' ', array_map(function($ar) {return $ar[1];}, $atext));
        $out[] = array_merge($g, array(
            'id' => $id,
            'text' => $groups_text[$id],
            'children_texts' => $atext
        ));
    }
}
function get_groups_by_sentence($sent_id, $user_id) {
    $simple = get_simple_groups_by_sentence($sent_id, $user_id);
    return array(
        #'simple' => array_filter($simple, function($el) {return $el['type'] != 16;}),
        'simple' => $simple,
        'complex' => get_complex_groups_by_simple($simple, $user_id)
    );
}
function add_group($parts, $type, $revset_id=0) {
    $is_complex = false;
    $ids = array();
    foreach ($parts as $i => $el) {
        if ($el['is_group'])
            $is_complex = true;
        $parts[$i]['id'] = (int)$el['id'];
        $ids[] = (int)$el['id'];
    }

    // TODO check complex groups too
    if (!$is_complex && !check_for_same_sentence($ids))
        return false;

    sql_begin();
    if (!$revset_id)
        $revset_id = create_revset();
    if (!$revset_id)
        return false;

    if (!group_type_exists($type))
        return false;

    if (!sql_query("INSERT INTO syntax_groups VALUES (NULL, $type, $revset_id, 0, ".$_SESSION['user_id'].")"))
        return false;
    $group_id = sql_insert_id();

    foreach ($parts as $el) {
        $token_id = $el['id'];
        if ($is_complex && !$el['is_group'])
            $token_id = get_dummy_group_for_token($token_id, true, $revset_id); 
        if (!$token_id)
            return false;
        if (!sql_query("INSERT INTO syntax_groups_".($is_complex ? "complex" : "simple")." VALUES ($group_id, $token_id)"))
            return false;
    }
    sql_commit();
    return $group_id;
}
function check_for_same_sentence($token_ids) {
    $res = sql_query_pdo("
        SELECT DISTINCT sent_id
        FROM text_forms
        WHERE tf_id IN (".join(',', $token_ids).")
    ");
    return (sql_num_rows($res) == 1);
}
function add_dummy_group($token_id, $revset_id=0) {
    sql_begin();
    if (!$revset_id)
        $revset_id = create_revset();
    $gid = add_group(array(array('id' => $token_id, 'is_group' => false)), 16, $revset_id);
    if (!$gid)
        return false;
    sql_commit();
    return $gid;
}
function get_dummy_group_for_token($token_id, $create_if_absent=true, $revset_id=0) {
    $res = sql_query_pdo("SELECT group_id FROM syntax_groups_simple WHERE group_type=16 AND token_id=$token_id");
    if (sql_num_rows($res) > 1)
        return false;
    if (sql_num_rows($res) == 1) {
        $r = sql_fetch_array($res);
        return $r['group_id'];
    }

    // therefore there is none
    if ($create_if_absent)
        return add_dummy_group($token_id, $revset_id);
    else
        return false;
}
function delete_group($group_id) {
    if (!is_group_owner($group_id, $_SESSION['user_id']))
        return false;
    sql_begin();
    if (
        !sql_query("DELETE FROM syntax_groups_simple WHERE group_id=$group_id") ||
        !sql_query("DELETE FROM syntax_groups_complex WHERE parent_gid=$group_id") ||
        !sql_query("DELETE FROM syntax_groups WHERE group_id=$group_id LIMIT 1")
    )
        return false;
    sql_commit();
    return true;
}
function set_group_head($group_id, $head_id) {
    // assume that the head of a complex group is also a group

    if (!is_group_owner($group_id, $_SESSION['user_id']))
        return false;

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
function set_group_type($group_id, $type_id) {
    if (!is_group_owner($group_id, $_SESSION['user_id'])) {
        return false;
    }
    if (!group_type_exists($type_id)) {
        return false;
    }
    return (bool)sql_query("UPDATE syntax_groups SET group_type=$type_id WHERE group_id=$group_id LIMIT 1");
}
function is_group_owner($group_id, $user_id) {
    $res = sql_query_pdo("SELECT * FROM syntax_groups WHERE group_id=$group_id AND user_id=$user_id LIMIT 1");
    return sql_num_rows($res) > 0;
}
function set_syntax_annot_status($book_id, $status) {
    if (!$book_id || !user_has_permission('perm_syntax') || !in_array($status, array(0, 1, 2)))
        return false;
    $user_id = $_SESSION['user_id'];
    sql_begin();
    if (!sql_query("DELETE FROM syntax_annotators WHERE user_id=$user_id AND book_id=$book_id"))
        return false;
    if ($status > 0)
        if (sql_query("INSERT INTO syntax_annotators VALUES($user_id, $book_id, $status)")) {
            sql_commit();
            return true;
        }
        else
            return false;
    sql_commit();
    return true;
}
?>
