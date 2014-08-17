<?php

function get_books_with_ne() {
    $res = sql_query("
        SELECT book_id, book_name
        FROM books
        WHERE ne_on = 1
        ORDER BY book_id
    ");
    $out = array();
    while ($r = sql_fetch_array($res))
        $out[] = array(
            'id' => $r['book_id'],
            'name' => $r['book_name']
        );
    return $out;
}

function get_ne_types() {
    $res = sql_query("SELECT tag_id, tag_name FROM ne_tags ORDER BY tag_id");
    $out = array();
    while ($r = sql_fetch_array($res))
        $out[] = array(
            'id' => $r['tag_id'],
            'name' => $r['tag_name']
        );
    return $out;
}

function get_ne_by_paragraph($par_id, $user_id) {
    if (!$user_id)
        throw new UnexpectedValueException();

    $res = sql_pe("
        SELECT entity_id, start_token, length
        FROM ne_entities
        JOIN ne_paragraphs USING (par_id)
        WHERE par_id=?
        AND user_id=?
    ", array($par_id, $user_id));
    $tag_res = sql_prepare("
        SELECT tag_id, tag_name
        FROM ne_entity_tags
        JOIN ne_tags USING (tag_id)
        WHERE entity_id = ?
    ");
    $out = array();

    foreach ($res as $r) {
        $entity = array(
            'id' => $r['entity_id'],
            'start_token' => $r['start_token'],
            'length' => $r['length'],
            'tokens' => array(),
            'tags' => array()
        );

        sql_execute($tag_res, array($r['entity_id']));
        while ($r1 = sql_fetch_array($tag_res))
            $entity['tags'][] = array($r1['tag_id'], $r1['tag_name']);

        $out[] = $entity;
    }
    $tag_res->closeCursor();

    // add token info
    $token_res = sql_prepare("
        SELECT tf_id, tf_text
        FROM tokens
        WHERE sent_id = (
            SELECT sent_id FROM tokens WHERE tf_id = ?
        )
        AND pos >= (
            SELECT pos FROM tokens WHERE tf_id = ?
        )
        ORDER BY pos
        LIMIT ?
    ");

    foreach ($out as &$entity) {
        sql_execute($token_res, array($entity['start_token'], $entity['start_token'], $entity['length']));
        while ($r = sql_fetch_array($token_res))
            $entity['tokens'][] = array($r['tf_id'], $r['tf_text']);

        if (sizeof($entity['tokens']) != $entity['length'])
            throw new Exception();
    }

    return $out;
}

function get_ne_tokens_by_paragraph($par_id, $user_id) {
    $entities = get_ne_by_paragraph($par_id, $user_id);
    $tokens = array();

    $res = sql_pe("
        SELECT tf_id
        FROM tokens
        JOIN sentences USING (sent_id)
        WHERE par_id = ?
    ", array($par_id));

    foreach ($res as $r)
        $tokens[$r['tf_id']] = array();

    foreach ($entities as $e)
        foreach ($e['tokens'] as $token_id)
            $tokens[$token_id][] = $e['tags'];

    return $tokens;
}

function get_ne_paragraph_status($book_id, $user_id) {
    $out = array(
        'unavailable' => array(),
        'started_by_user' => array(),
        'done_by_user' => array()
    );
    $res = sql_pe("
        SELECT par_id, status, user_id, ts_finish
        FROM ne_paragraphs
        JOIN paragraphs USING (par_id)
        WHERE book_id = ?
        ORDER BY par_id
    ", array($book_id));

    $cur_pid = 0;
    $occupied_num = 0;
    $started = false;
    $done = false;
    $now = time();

    foreach ($res as $r) {
        if ($cur_pid && $cur_pid != $r['par_id']) {
            if ($occupied_num >= 3 && !$done)
                $out['unavailable'][] = $cur_pid;
            elseif ($started)
                $out['started_by_user'][] = $cur_pid;
            elseif ($done)
                $out['done_by_user'][] = $cur_pid;

            $occupied_num = 0;
            $started = $done = false;
        }
        if ($user_id == $r['user_id']) {
            if ($r['status'] == NE_STATUS_FINISHED)
                $done = true;
            elseif ($r['status'] == NE_STATUS_IN_PROGRESS)
                $started = true;
        }
        else {
            if (
                $r['status'] == NE_STATUS_FINISHED ||
                ($r['status'] == NE_STATUS_IN_PROGRESS && $r['ts_finish'] < $now)
            )
                ++$occupied_num;
        }
        $cur_pid = $r['par_id'];
    }

    // last row
    if ($cur_pid) {
        if ($occupied_num >= 3 && !$done)
            $out['unavailable'][] = $cur_pid;
        elseif ($started)
            $out['started_by_user'][] = $cur_pid;
        elseif ($done)
            $out['done_by_user'][] = $cur_pid;
    }

    // last row
    if ($cur_pid) {
        if ($occupied_num >= 3 && !$done)
            $out['unavailable'][] = $cur_pid;
        elseif ($started)
            $out['started_by_user'][] = $cur_pid;
        elseif ($done)
            $out['done_by_user'][] = $cur_pid;
    }

    return $out;
}

function start_ne_annotation($par_id) {
    if (!$par_id)
        throw new UnexpectedValueException();

    $user_id = $_SESSION['user_id'];

    // TODO make something with another user's deserted annotation if it exists

    sql_pe("
        INSERT INTO ne_paragraphs
        VALUES (?, ?, ?, ?)
    ", array($par_id, $user_id, NE_STATUS_IN_PROGRESS, time() + NE_ANNOT_TIMEOUT));
}

function finish_ne_annotation($par_id) {
    if (!$par_id)
        throw new UnexpectedValueException();

    $user_id = $_SESSION['user_id'];

    if (!check_ne_paragraph_status($par_id, $user_id))
        throw new Exception();

    sql_pe("
        UPDATE ne_paragraphs
        SET status = ?
        WHERE par_id = ?
        AND user_id = ?
        LIMIT 1
    ", array(NE_STATUS_FINISHED, $par_id, $user_id));
}

function check_ne_paragraph_status($par_id, $user_id) {
    // returns true iff user can modify the annotation
    $res = sql_pe("
        SELECT par_id
        FROM ne_paragraphs
        WHERE par_id = ?
        AND user_id = ?
        AND `status` = ".NE_STATUS_IN_PROGRESS."
        LIMIT 1
    ", array($par_id, $user_id));
    return sizeof($res) > 0;
}

function add_ne_annotation($par_id, $token_ids, $tags) {
    // TODO check that tokens follow each other within the same sentence
    // for now presume that $token_ids[0] is the starting token
    if (!check_ne_paragraph_status($par_id, $_SESSION['user_id']))
        throw new Exception();

    sql_begin();
    sql_pe("
        INSERT INTO ne_entities
        VALUES (NULL, ?, ?, ?, ?)
    ", array($par_id, $token_ids[0], sizeof($token_ids), time()));

    $entity_id = sql_insert_id();
    set_ne_tags($entity_id, $tags, $par_id);
    sql_commit();
    return $entity_id;
}

function delete_ne_annotation($entity_id) {
    $res = sql_pe("
        SELECT par_id
        FROM ne_entities
        WHERE entity_id = ?
    ", array($entity_id));
    if (!check_ne_paragraph_status($par_id, $_SESSION['user_id']))
        throw new Exception();

    sql_begin();
    sql_pe("DELETE FROM ne_entity_tags WHERE entity_id=?", array($entity_id));
    sql_pe("DELETE FROM ne_entities WHERE entity_id=?", array($entity_id));
    sql_commit();
}

function set_ne_tags($entity_id, $tags, $par_id=0) {
    // overwrites old set of tags
    if (!check_ne_paragraph_status($par_id, $_SESSION['user_id']))
        throw new Exception();

    if (!$par_id) {
        $res = sql_pe("SELECT par_id FROM ne_entities WHERE entity_id = ?", array($entity_id));
        $par_id = $res[0];
    }

    sql_begin();
    sql_pe("DELETE FROM ne_entity_tags WHERE entity_id = ?", array($entity_id));
    $res = sql_prepare("INSERT INTO ne_entity_tags VALUES(?, ?)");
    foreach ($tags as $tag)
        sql_execute($res, array($entity_id, $tag));

    sql_commit();
}
?>
