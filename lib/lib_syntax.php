<?php
require_once('lib_books.php');
function get_books_with_syntax() {
    $res = sql_query_pdo("SELECT book_id, status, user_id FROM anaphora_syntax_annotators");
    $syntax = array();
    while ($r = sql_fetch_array($res)) {
        if (!isset($syntax[$r['book_id']]))
            $syntax[$r['book_id']] = array(1 => 0, 2 => 0);
        if ($r['user_id'] == $_SESSION['user_id'])
            $syntax[$r['book_id']]['self'] = $r['status'];
        $syntax[$r['book_id']][$r['status']] += 1;
    }

    $res = sql_query_pdo("
        SELECT book_id, book_name, old_syntax_moder_id, COUNT(tf_id) AS token_count, syntax_on
        FROM books
            JOIN paragraphs
                USING (book_id)
            JOIN sentences
                USING (par_id)
            JOIN tokens
                USING (sent_id)
        WHERE syntax_on > 0
        GROUP BY book_id
        ORDER BY book_id
    ");
    $out = array(
        'books' => array(),
        'token_count' => 0
    );
    while ($r = sql_fetch_array($res)) {
        $out['books'][] = array(
            'id' => $r['book_id'],
            'name' => $r['book_name'],
            'first_sentence_id' => get_book_first_sentence_id($r['book_id']),
            'syntax_moder_id' => $r['old_syntax_moder_id'],
            'status' => array(
                'syntax' => array(
                    'self' => isset($syntax[$r['book_id']]['self']) ? $syntax[$r['book_id']]['self'] : 0,
                    'total' => isset($syntax[$r['book_id']]) ? $syntax[$r['book_id']] : array(1 => 0, 2 => 0),
                    'moderated' => $r['syntax_on'] > 1 ? true : false
                ),
                'anaphor' => 0
            )
        );
        $out['token_count'] += $r['token_count'];
    }
    return $out;
}
function get_syntax_group_types() {
    $res = sql_query_pdo("SELECT type_id, type_name FROM anaphora_syntax_group_types ORDER BY type_name");
    $out = array();
    while ($r = sql_fetch_array($res))
        $out[$r['type_id']] = $r['type_name'];
    return $out;
}
function group_type_exists($type) {
    if ($type == 0)
        return true;
    $res = sql_pe("SELECT type_id FROM anaphora_syntax_group_types WHERE type_id=? LIMIT 1", array($type));
    return sizeof($res) > 0;
}

function get_group_text($group_id) {
    $texts = array();
    $res = sql_query_pdo("SELECT * FROM anaphora_syntax_groups_simple WHERE group_id = $group_id");
    $r = sql_fetchall($res);
    if (!empty($r)) {
        $token_ids = array_reduce($r, function($ids, $el) {
            if ($ids) return $ids.','.$el['token_id'];
            return $el['token_id'];
        });

        $tokens_res = sql_query_pdo("SELECT tf_text FROM tokens WHERE tf_id IN ($token_ids)");
        while ($r = sql_fetch_array($tokens_res)) {
            $texts[] = $r['tf_text'];
        }
        return join(" ", $texts);
    }

    $res = sql_query_pdo("SELECT * FROM anaphora_syntax_groups_complex WHERE parent_gid = $group_id");
    $r = sql_fetch_array($res);

    if (!empty($r)) {
        $token_ids = get_group_tokens($group_id);
        $token_ids = join(',', $token_ids);
        $tokens_res = sql_query_pdo("SELECT tf_text FROM tokens WHERE tf_id IN ($token_ids)");
        while ($r = sql_fetch_array($tokens_res)) {
            $texts[] = $r['tf_text'];
        }
        return join(" ", $texts);
    }
}

function get_group_tokens($group_id) {
    $tokens = array();
    $simple_groups = get_simple_groups_by_complex($group_id);
    $gr_ids = join(',', $simple_groups);
    $tokens_res = sql_query_pdo($gr_ids ?
        "SELECT token_id FROM anaphora_syntax_groups_simple WHERE group_id IN ($gr_ids)" :
        "SELECT token_id FROM anaphora_syntax_groups_simple WHERE group_id = $group_id");
    while ($r = sql_fetch_array($tokens_res)) {
        $tokens[] = (int)$r['token_id'];
    }
    return $tokens;
}


function get_simple_groups_by_complex($group_id) {
    $simple = array();
    $frontier = array();
    $get_children = "SELECT child_gid FROM anaphora_syntax_groups_complex WHERE parent_gid = ";
    $res = sql_query_pdo($get_children . $group_id);

    $frontier = array_map(function($row) {
        return $row['child_gid'];
    }, sql_fetchall($res));

    while (!empty($frontier)) {
        $gid = array_pop($frontier);
        $res = sql_query_pdo($get_children . $gid);
        $r = sql_fetchall($res);

        if ($r) {
            foreach ($r as $row) {
                $frontier[] = $row['child_gid'];
            }
        } else {
            $simple[] = $gid;
        }
    }

    return $simple;
}

function get_simple_groups_by_sentence($sent_id, $user_id) {
    $out = array();
    $res = sql_query_pdo("
        SELECT group_id, group_type, token_id, tf_text, head_id, tf.pos
        FROM anaphora_syntax_groups_simple sg
        JOIN anaphora_syntax_groups g USING (group_id)
        JOIN tokens tf ON (sg.token_id = tf.tf_id)
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
    $possible_children = array(0);
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
            FROM anaphora_syntax_groups g
            JOIN anaphora_syntax_groups_complex gc
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
                // make sure that all the children are already added before their parent is added
                $res1 = sql_query_pdo("
                    SELECT child_gid
                    FROM anaphora_syntax_groups_complex
                    WHERE parent_gid = ".$r['parent_gid']."
                    AND child_gid NOT IN (".join(',', $possible_children).")
                ");
                if (sql_num_rows($res1) == 0) {
                    $possible_children[] = $r['parent_gid'];
                    $groups[$r['parent_gid']] = array(
                        'type' => $r['group_type'],
                        'children' => array($r['child_gid']),
                        'head_id' => $r['head_id'],
                        'start_pos' => $groups_pos[$r['child_gid']]
                    );
                }
            }
            $groups_pos[$r['parent_gid']] = $groups[$r['parent_gid']]['start_pos'];
        }
    }

    $out = array();
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
            'children_texts' => $atext,
        ));
    }
    return $out;
}
function get_groups_by_sentence($sent_id, $user_id) {
    $simple = get_simple_groups_by_sentence($sent_id, $user_id);
    return array(
        'simple' => $simple,
        'complex' => get_complex_groups_by_simple($simple, $user_id)
    );
}

function get_moderated_groups_by_token($token_id, $in_head = FALSE) {
    $res = sql_query_pdo("
        SELECT sent_id, tf_text
        FROM tokens
        WHERE tf_id = $token_id
    ");

    $r = sql_fetch_array($res);
    $sent_id = $r['sent_id'];
    $token = $r['tf_text'];

    $groups = get_moderated_groups_by_sentence($sent_id);
    $simple_groups = array();
    $complex_groups = array();

    foreach ($groups['simple'] as $k => $group) {
        if (in_array($token_id, $group['tokens'])) {
            $simple_groups[] = $group;
        }
    }

    foreach ($groups['complex'] as $k => $group) {
        // костыль
        $text = ' '. $group['text']. ' ';
        $t = ' '. $token. ' ';
        if (mb_strpos($text, $t) !== FALSE) {
            $complex_groups[] = $group;
        }
    }

    return array(
        'simple' => $simple_groups,
        'complex' => $complex_groups
    );
}

function get_all_groups_by_sentence($sent_id) {
    $res = sql_query_pdo("
        SELECT DISTINCT user_id
        FROM anaphora_syntax_groups_simple sgs
        JOIN anaphora_syntax_groups sg USING (group_id)
        JOIN tokens tf ON (sgs.token_id = tf.tf_id)
        WHERE sent_id = $sent_id
    ");
    $out = array();

    while ($r = sql_fetch_array($res)) {
        $out[$r['user_id']] = get_groups_by_sentence($sent_id, $r['user_id']);
    }

    return $out;
}


function get_pronouns_by_sentence($sent_id) {
    $token_ids = array();
    $res = sql_query_pdo("
        SELECT tf_id
        FROM tokens
        LEFT JOIN tf_revisions
            USING (tf_id)
        WHERE
            sent_id=$sent_id
            AND is_last = 1
            AND rev_text LIKE '%<g v=\"Anph\"/>%'
    ");
    while ($r = sql_fetch_array($res)) {
        array_push($token_ids, $r['tf_id']);
    }
    return $token_ids;
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
        throw new Exception();

    sql_begin();
    if (!$revset_id)
        $revset_id = create_revset();

    if (!group_type_exists($type))
        throw new Exception();

    sql_query_pdo("INSERT INTO anaphora_syntax_groups VALUES (NULL, $type, $revset_id, 0, ".$_SESSION['user_id'].", '')");
    $group_id = sql_insert_id_pdo();

    foreach ($parts as $el) {
        $token_id = $el['id'];
        if ($is_complex && !$el['is_group'])
            $token_id = get_dummy_group_for_token($token_id, true, $revset_id);
        sql_query_pdo("INSERT INTO anaphora_syntax_groups_".($is_complex ? "complex" : "simple")." VALUES ($group_id, $token_id)");
    }
    sql_commit();
    return $group_id;
}
function check_for_same_sentence($token_ids) {
    $res = sql_query_pdo("
        SELECT DISTINCT sent_id
        FROM tokens
        WHERE tf_id IN (".join(',', $token_ids).")
    ");
    return (sql_num_rows($res) == 1);
}
function add_dummy_group($token_id, $revset_id=0) {
    sql_begin();
    if (!$revset_id)
        $revset_id = create_revset();
    $gid = add_group(array(array('id' => $token_id, 'is_group' => false)), 16, $revset_id);
    sql_commit();
    return $gid;
}
function get_dummy_group_for_token($token_id, $create_if_absent=true, $revset_id=0) {
    $res = sql_query_pdo("SELECT group_id FROM anaphora_syntax_groups_simple WHERE group_type=16 AND token_id=$token_id");
    if (sql_num_rows($res) > 1)
        throw new Exception();
    if (sql_num_rows($res) == 1) {
        $r = sql_fetch_array($res);
        return $r['group_id'];
    }

    // therefore there is none
    if ($create_if_absent)
        return add_dummy_group($token_id, $revset_id);
    else
        throw new Exception();
}
function delete_group($group_id) {
    if (!is_group_owner($group_id, $_SESSION['user_id']))
        throw new Exception();

    // forbid deletion if group is part of another group
    $res = sql_pe("SELECT * FROM anaphora_syntax_groups_complex WHERE child_gid=? LIMIT 1", array($group_id));
    if (sizeof($res) > 0)
        throw new Exception();

    sql_begin();
    sql_pe("DELETE FROM anaphora_syntax_groups_simple WHERE group_id=?", array($group_id));
    sql_pe("DELETE FROM anaphora_syntax_groups_complex WHERE parent_gid=?", array($group_id));
    sql_pe("DELETE FROM anaphora_syntax_groups WHERE group_id=? LIMIT 1", array($group_id));
    sql_commit();
}
function set_group_head($group_id, $head_id) {
    // assume that the head of a complex group is also a group

    if (!is_group_owner($group_id, $_SESSION['user_id']))
        throw new Exception();

    // check if head belongs to the group
    $res = sql_pe(
        "SELECT * FROM anaphora_syntax_groups_simple WHERE group_id=? AND token_id=? LIMIT 1",
        array($group_id, $head_id)
    );
    if (!sizeof($res)) {
        // perhaps the group is complex then
        $res = sql_pe(
            "SELECT * FROM anaphora_syntax_groups_complex WHERE parent_gid=? AND child_gid=?",
            array($group_id, $head_id)
        );
        if (!sizeof($res))
            throw new Exception();
    }

    // set the head
    sql_pe(
        "UPDATE anaphora_syntax_groups SET head_id=? WHERE group_id=? LIMIT 1",
        array($head_id, $group_id)
    );
}
function set_group_type($group_id, $type_id) {
    if (!is_group_owner($group_id, $_SESSION['user_id'])) {
        throw new Exception();
    }
    if (!group_type_exists($type_id)) {
        throw new UnexpectedValueException();
    }
    sql_pe(
        "UPDATE anaphora_syntax_groups SET group_type=? WHERE group_id=? LIMIT 1",
        array($type_id, $group_id)
    );
}
function is_group_owner($group_id, $user_id) {
    $res = sql_pe("SELECT * FROM anaphora_syntax_groups WHERE group_id=? AND user_id=? LIMIT 1", array($group_id, $user_id));
    return sizeof($res) > 0;
}
function set_syntax_annot_status($book_id, $status) {
    if (!$book_id || !in_array($status, array(0, 1, 2)))
        throw new UnexpectedValueException();
    if (!user_has_permission('perm_syntax'))
        throw new Exception("Недостаточно прав");
    $user_id = $_SESSION['user_id'];
    sql_begin();
    sql_pe("DELETE FROM anaphora_syntax_annotators WHERE user_id=? AND book_id=?", array($user_id, $book_id));
    if ($status > 0)
        sql_pe("INSERT INTO anaphora_syntax_annotators VALUES (?, ?, ?)", array($user_id, $book_id, $status));
    sql_commit();
}

// SYNTAX MODERATION

function become_syntax_moderator($book_id) {
    if (!$book_id)
        throw new UnexpectedValueException();
    if (!user_has_permission('perm_syntax'))
        throw new Exception("Недостаточно прав");

    $res = sql_pe("
        SELECT old_syntax_moder_id AS mid
        FROM books
        WHERE book_id=?
        LIMIT 1
    ", array($book_id));
    if ($res[0]['mid'] > 0)
        throw new Exception("Место модератора занято");

    sql_pe("
        UPDATE books
        SET old_syntax_moder_id = ?
        WHERE book_id = ?
        LIMIT 1
    ", array($_SESSION['user_id'], $book_id));
}

function finish_syntax_moderation($book_id) {
    if (!$book_id)
        throw new UnexpectedValueException();
    if (!user_has_permission('perm_syntax'))
        throw new Exception("Недостаточно прав");

    $res = sql_pe("
        SELECT old_syntax_moder_id AS mid
        FROM books
        WHERE book_id = ?
        LIMIT 1
    ", array($book_id));
    if ($res[0]['mid'] != $_SESSION['user_id'])
        throw new Exception("Вы не модератор");
    
    sql_pe("
        UPDATE books
        SET syntax_on = 2
        WHERE book_id = ?
        LIMIT 1
    ", array($book_id));
}

function copy_group($source_group_id, $dest_user, $revset_id=0) {
    if (!user_has_permission('perm_syntax'))
        throw new Exception();
    if (!$source_group_id || !$dest_user)
        throw new UnexpectedValueException();
    sql_begin();

    if (!$revset_id)
        $revset_id = create_revset();

    sql_query_pdo("
        INSERT INTO anaphora_syntax_groups
        (
            SELECT NULL, group_type, $revset_id, head_id, $dest_user, marks
            FROM anaphora_syntax_groups
            WHERE group_id = $source_group_id
            LIMIT 1
        )
    ");
    $copy_id = sql_insert_id_pdo();

    // save head
    $r = sql_fetch_array(sql_query_pdo("SELECT head_id FROM anaphora_syntax_groups WHERE group_id = $copy_id LIMIT 1"));
    $head_id = $r['head_id'];

    // simple group
    copy_simple_group($source_group_id, $copy_id);

    // complex group (recursive)
    $res = sql_query_pdo("
        SELECT child_gid
        FROM anaphora_syntax_groups_complex
        WHERE parent_gid = $source_group_id
    ");

    while ($r = sql_fetch_array($res)) {
        $gid = copy_group($r['child_gid'], $dest_user, $revset_id);
        sql_query_pdo("INSERT INTO anaphora_syntax_groups_complex VALUES ($copy_id, $gid)");
        if ($r['child_gid'] == $head_id)
            $head_id = $gid;
    }

    // update head
    sql_query_pdo("UPDATE anaphora_syntax_groups SET head_id=$head_id WHERE group_id=$copy_id LIMIT 1");

    sql_commit();
    return $copy_id;
}
function copy_simple_group($source_group_id, $dest_group_id) {
    sql_query_pdo("
        INSERT INTO anaphora_syntax_groups_simple
        (
            SELECT $dest_group_id, token_id
            FROM anaphora_syntax_groups_simple
            WHERE group_id = $source_group_id
        )
    ");
}

function get_sentence_moderator($sent_id) {
    $res = sql_pe("
        SELECT old_syntax_moder_id AS mid
        FROM sentences
            JOIN paragraphs USING (par_id)
            JOIN books USING (book_id)
        WHERE sent_id=?
        LIMIT 1
    ", array($sent_id));
    return $res[0]['mid'];
}

function get_moderated_groups_by_sentence($sent_id) {
    return get_groups_by_sentence($sent_id, get_sentence_moderator($sent_id));
}

// ANAPHORA

function add_anaphora($anaphor_id, $antecedent_id) {
    // check that anaphor exists and has Anph grammeme
    $res = sql_query_pdo("SELECT rev_text FROM tf_revisions WHERE tf_id=$anaphor_id AND is_last=1 LIMIT 1");
    if (sql_num_rows($res) == 0)
        throw new Exception();
    $r = sql_fetch_array($res);

    if (strpos($r['rev_text'], '<g v="Anph"/>') === false)
        throw new Exception();
    // check that antecedent exists
    $res = sql_query_pdo("SELECT * FROM anaphora_syntax_groups WHERE group_id=$antecedent_id LIMIT 1");
    if (sql_num_rows($res) == 0)
        throw new Exception();

    // TODO check that the group belongs to the moderator
    // TODO check that both token and group are within one book

    $revset_id = create_revset();

    sql_query_pdo("INSERT INTO anaphora VALUES (NULL, $anaphor_id, $antecedent_id, $revset_id, ".$_SESSION['user_id'].")");
    return sql_insert_id_pdo();
}

function delete_anaphora($ref_id) {
    sql_pe("DELETE FROM anaphora WHERE ref_id=? LIMIT 1", array($ref_id));
}

function get_anaphora_by_book($book_id) {
    $res = sql_pe("
        SELECT token_id, group_id, ref_id, tf.tf_text as token
        FROM anaphora a
            JOIN tokens tf ON (a.token_id = tf.tf_id)
            JOIN sentences USING (sent_id)
            JOIN paragraphs USING (par_id)
        WHERE book_id = ?
    ", array($book_id));
    $out = array();
    foreach ($res as $r) {
        $out[$r['ref_id']] = $r;
        $out[$r['ref_id']]['group_text'] = get_group_text($r['group_id']);
        $out[$r['ref_id']]['group_tokens'] = get_group_tokens($r['group_id']);
    }
    return $out;
}
