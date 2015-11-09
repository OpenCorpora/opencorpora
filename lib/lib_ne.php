<?php
require_once('constants.php');
require_once('lib_users.php');

function get_current_tagset() {
    check_logged();
    return OPTION(OPT_NE_TAGSET);
}

function get_books_with_ne($tagset_id) {
    $total = sql_pe("
        SELECT COUNT(book_id) AS total
        FROM ne_books_tagsets
        LEFT JOIN paragraphs USING (book_id)
        LEFT JOIN (
            SELECT par_id, COUNT(annot_id) as fin
            FROM ne_paragraphs
            WHERE tagset_id = ?
            AND status >= 2
            GROUP BY par_id
            HAVING fin >= ".NE_ANNOTATORS_PER_TEXT."
        ) T USING (par_id)
        GROUP BY book_id
        HAVING COUNT(par_id) = COUNT(fin)
    ", array($tagset_id));
    $out = array('books' => array(), 'ready' => sizeof($total));

    $res = sql_pe("
        SELECT book_id, book_name, par_id, status, user_id
        FROM books
        LEFT JOIN ne_books_tagsets bs
            USING (book_id)
        LEFT JOIN paragraphs
            USING (book_id)
        LEFT JOIN ne_paragraphs
            USING (par_id)
        WHERE bs.tagset_id = ?
        ORDER BY book_id, par_id
    ", array($tagset_id));
    $allbooks = array();
    $book = array(
        'num_par' => 0,
        'ready_annot' => 0,
        'available' => true,
        'started' => 0,
        'all_ready' => false
    );
    $last_book_id = 0;
    $last_par_id = 0;
    $finished_annot = 0;
    $finished_by_me = 0;
    foreach ($res as $r) {
        if ($r['par_id'] != $last_par_id) {
            if ($last_par_id) {
                $book['num_par'] += 1;
                $book['ready_annot'] += min($finished_annot, NE_ANNOTATORS_PER_TEXT);
            }
            $finished_annot = 0;
        }
        if ($r['book_id'] != $last_book_id && $last_book_id) {
            $book['all_ready'] = ($book['ready_annot'] >= NE_ANNOTATORS_PER_TEXT * $book['num_par']);
            $book['available'] = ($finished_by_me < $book['num_par']) && !$book['all_ready'];
            if (!$book['all_ready']) {
                $out['books'][] = $book;
                if (sizeof($out['books']) >= NE_ACTIVE_BOOKS)
                    break;
            }
            $book = array(
                'num_par' => 0,
                'ready_annot' => 0,
                'available' => true,
                'started' => 0,
                'all_ready' => false
            );
            $finished_by_me = 0;
        }

        if ($r['status'] == NE_STATUS_FINISHED) {
            $finished_annot += 1;
            if (is_logged() && $r['user_id'] == $_SESSION['user_id']) {
                $finished_by_me += 1;
                $book['started'] = 1;
            }
        }

        $book['id'] = $r['book_id'];
        $book['name'] = $r['book_name'];
        $allbooks[$book['id']] = true;
        $book['queue_num'] = sizeof($allbooks);
        $last_book_id = $r['book_id'];
        $last_par_id = $r['par_id'];
    }
    $book['num_par'] += 1;
    $book['ready_annot'] += max($finished_annot, NE_ANNOTATORS_PER_TEXT);
    $book['all_ready'] = ($book['ready_annot'] >= NE_ANNOTATORS_PER_TEXT * $book['num_par']);
    $book['available'] = ($finished_by_me < $book['num_par']) && !$book['all_ready'];
    if (!$book['all_ready'] && sizeof($out['books']) < NE_ACTIVE_BOOKS)
        $out['books'][] = $book;

    // sort so that unavailable texts go last
    /* uasort($out, function($a, $b) {
        if ($a['available'] == $b['available'])
            return $a['started'] < $b['started'] ? 1: -1;
        return $a['available'] < $b['available'] ? 1 : -1;
    }); */
    return $out;
}

function get_ne_types($tagset_id) {
    $res = sql_pe("SELECT tag_id, tag_name, color_number
        FROM ne_tags WHERE tagset_id=? ORDER BY tag_id", array($tagset_id));
    $out = array();
    foreach ($res as $r)
        $out[$r['tag_id']] = array(
            'id' => $r['tag_id'],
            'name' => $r['tag_name'],
            'color' => $r['color_number']
        );
    return $out;
}

function get_object_types($tagset_id) {
    $res = sql_pe("SELECT object_type_id, object_name, color_number FROM ne_object_types WHERE tagset_id=? ORDER BY object_type_id", array($tagset_id));
    $out = array();
    foreach ($res as $r)
        $out[$r['object_type_id']] = array(
            'id' => $r['object_type_id'],
            'name' => $r['object_name'],
            'color' => $r['color_number']
        );
    return $out;
}

function get_ne_entity_tokens_info($start_token_id, $length) {
    static $token_res = NULL;

    if ($token_res == NULL) {
        $token_res = sql_prepare("
            SELECT tf_id, tf_text, pos, sent_id
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
    }

    $out = array();
    sql_execute($token_res, array($start_token_id, $start_token_id, $length));
    while ($r = sql_fetch_array($token_res))
        $out[] = array($r['tf_id'], $r['tf_text'],
            'pos' => $r['pos'], 'sent_id' => $r['sent_id']);

    return $out;
}

function group_entities_by_mention($entities) {
    $new = array();

    foreach ($entities as $e) {
        $mids = $e['mention_ids'];
        foreach ($mids as $i => $mid) {
            if (!isset($new[$mid]))
                $new[$mid] = array('entities' => array(), 'type' => $e['mention_types'][$i]);
            $new[$mid]['entities'][] = $e;
        }
    }

    return $new;
}

function get_ne_by_paragraph($par_id, $user_id, $tagset_id, $group_by_mention = false) {
    if (!$user_id)
        throw new UnexpectedValueException();

    $res = sql_pe("
        SELECT annot_id
        FROM ne_paragraphs
        WHERE par_id = ?
        AND user_id = ?
        AND tagset_id = ?
        LIMIT 1
    ", array($par_id, $user_id, $tagset_id));

    if (!sizeof($res))
        return array();

    $out = array(
        'annot_id' => $res[0]['annot_id'],
        'entities' => array()
    );

    $res = sql_query("
        SELECT entity_id, start_token, length, mention_id, object_type_id
        FROM ne_entities
        LEFT JOIN ne_entities_mentions
            USING (entity_id)
        LEFT JOIN ne_mentions
            USING (mention_id)
        WHERE annot_id=".$out['annot_id']
    );
    $tag_res = sql_prepare("
        SELECT tag_id, tag_name
        FROM ne_entity_tags
        JOIN ne_tags USING (tag_id)
        WHERE entity_id = ?
    ");

    while ($r = sql_fetch_array($res)) {
        $eid = $r['entity_id'];
        if (isset($out['entities'][$eid])) {
            $out['entities'][$eid]['mention_ids'][] = $r['mention_id'];
            $out['entities'][$eid]['mention_types'][] = $r['object_type_id'];
            continue;
        }

        $entity = array(
            'id' => $r['entity_id'],
            'start_token' => $r['start_token'],
            'length' => $r['length'],
            'tokens' => array(),
            'mention_ids' => array($r['mention_id']),
            'mention_types' => array($r['object_type_id']),
            'tags' => array(),
            'tag_ids' => array()
        );

        if (empty($r['mention_id'])) {
            $entity['mention_ids'] = array();
            $entity['mention_types'] = array();
        }

        sql_execute($tag_res, array($eid));
        while ($r1 = sql_fetch_array($tag_res)) {
            $entity['tags'][] = array($r1['tag_id'], $r1['tag_name']);
            $entity['tag_ids'][] = $r1['tag_id'];
        }
        // TODO check that tags belong to the correct tagset

        $out['entities'][$eid] = $entity;
    }
    $tag_res->closeCursor();

    // add token info
    foreach ($out['entities'] as &$entity) {
        $entity['tokens'] = get_ne_entity_tokens_info($entity['start_token'], $entity['length']);
        if (sizeof($entity['tokens']) != $entity['length'])
            throw new Exception("len of entity tokens != entity.length, entity ".$entity['id']);
    }

    // sort entities by position in paragraph (by first token pos)
    usort($out['entities'], function($e1, $e2) {
        $s1 = $e1['tokens'][0]['sent_id'];
        $s2 = $e2['tokens'][0]['sent_id'];
        $pos1 = $e1['tokens'][0]['pos'];
        $pos2 = $e2['tokens'][0]['pos'];

        return ($s1 == $s2 ? ($pos1 - $pos2) : ($s1 - $s2));
    });

    if ($group_by_mention)
        $out['entities'] = group_entities_by_mention($out['entities']);

    return $out;
}

function get_all_ne_by_sentence($sent_id) {
    return sql_pe("
        SELECT entity_id, start_token, length
        FROM ne_entities
        WHERE start_token IN (
            SELECT tf_id
            FROM tokens
            WHERE sent_id = ?
        )
        LIMIT 1
    ", array($sent_id));
}

function get_ne_tokens_by_paragraph($par_id, $user_id, $tagset_id) {
    $annot = get_ne_by_paragraph($par_id, $user_id, $tagset_id);
    $tokens = array();

    $res = sql_pe("
        SELECT tf_id
        FROM tokens
        JOIN sentences USING (sent_id)
        WHERE par_id = ?
    ", array($par_id));

    foreach ($res as $r)
        $tokens[$r['tf_id']] = array();

    if (empty($annot['entities'])) return $tokens;

    foreach ($annot['entities'] as $e) {
        foreach ($e['tokens'] as $token) {
            $ne = array('tags' => $e['tags'], 'entity_id' => $e['id']);
            $tokens[$token[0]][] = $ne;
        }
    }
    return $tokens;
}

function get_comments_by_paragraph($par_id, $user_id, $tagset_id) {
    $res = sql_pe("
        SELECT *
        FROM ne_paragraph_comments npc
        LEFT JOIN ne_paragraphs np
            USING (annot_id)
        WHERE np.par_id = ?
        AND npc.user_id = ?
        AND tagset_id = ?",
        array($par_id, $user_id, $tagset_id));
    return $res;
}

// for moderator
function get_all_comments_by_paragraph($par_id, $tagset_id) {
    $res = sql_pe("
        SELECT *
        FROM ne_paragraph_comments
        LEFT JOIN ne_paragraphs
            USING (annot_id)
        WHERE par_id = ?
        AND tagset_id = ?",
        array($par_id, $tagset_id));
    return $res;
}

function add_comment_to_paragraph($par_id, $user_id, $comment) {
    // current design allows user to add comments to his own paragraphs only
    // though database allows comments to anyone's paragraphs
    $tres = sql_pe("SELECT annot_id FROM ne_paragraphs WHERE user_id=? and par_id=?", array($user_id, $par_id));
    if (sizeof($tres) > 1 || sizeof($tres) == 0)
        throw new Exception();
    $annot_id = $tres[0]['annot_id'];
    $res = sql_pe("
        INSERT INTO ne_paragraph_comments
        (annot_id, user_id, comment)
        VALUES (?, ?, ?)",
        array($annot_id, $user_id, $comment));
    return sql_insert_id();
}

function get_ne_paragraph_status($book_id, $user_id, $tagset_id) {
    $out = array(
        'unavailable' => array(),
        'started_by_user' => array(),
        'done_by_user' => array()
    );
    $res = sql_pe("
        SELECT par_id, status, user_id, started_ts
        FROM ne_paragraphs
        JOIN paragraphs USING (par_id)
        WHERE book_id = ?
        AND tagset_id = ?
        ORDER BY par_id
    ", array($book_id, $tagset_id));

    $cur_pid = 0;
    $occupied_num = 0;
    $started = false;
    $done = false;
    $now = time();

    foreach ($res as $r) {
        if ($cur_pid && $cur_pid != $r['par_id']) {
            if ($occupied_num >= NE_ANNOTATORS_PER_TEXT && !$done)
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
                ($r['status'] == NE_STATUS_IN_PROGRESS && $r['started_ts'] < ($now + NE_ANNOT_TIMEOUT))
            )
                ++$occupied_num;
        }
        $cur_pid = $r['par_id'];
    }

    // last row
    if ($cur_pid) {
        if ($occupied_num >= NE_ANNOTATORS_PER_TEXT && !$done)
            $out['unavailable'][] = $cur_pid;
        elseif ($started)
            $out['started_by_user'][] = $cur_pid;
        elseif ($done)
            $out['done_by_user'][] = $cur_pid;
    }

    return $out;
}

function start_ne_annotation($par_id, $tagset_id) {
    if (!$par_id)
        throw new UnexpectedValueException();

    $user_id = $_SESSION['user_id'];

    // TODO make something with another user's deserted annotation if it exists

    // check that the paragraph doesn't yet exist
    $res = sql_pe("
        SELECT annot_id
        FROM ne_paragraphs
        WHERE user_id = ?
        AND par_id = ?
        AND tagset_id = ?
        LIMIT 1
    ", array($user_id, $par_id, $tagset_id));

    if (sizeof($res) > 0)
        throw new Exception();

    sql_pe("
        INSERT INTO ne_paragraphs
        VALUES (NULL, ?, ?, ?, ?, ?, ?)
    ", array($par_id, $user_id, NE_STATUS_IN_PROGRESS, time(), 0, $tagset_id));

    return sql_insert_id();
}

function finish_ne_annotation($annot_id) {
    if (!$annot_id)
        throw new UnexpectedValueException();

    $user_id = $_SESSION['user_id'];

    if (!check_ne_paragraph_status($annot_id, $user_id))
        throw new Exception();

    sql_pe("
        UPDATE ne_paragraphs
        SET status = ?, finished_ts = ?
        WHERE annot_id = ?
        LIMIT 1
    ", array(NE_STATUS_FINISHED, time(), $annot_id));
}

function check_ne_paragraph_status($annot_id, $user_id) {
    // returns true iff user can modify the annotation
    $res = sql_pe("
        SELECT par_id
        FROM ne_paragraphs
        WHERE annot_id = ?
        AND user_id = ?
        AND `status` = ".NE_STATUS_IN_PROGRESS."
        LIMIT 1
    ", array($annot_id, $user_id));
    return sizeof($res) > 0;
}

function add_ne_entity($annot_id, $token_ids, $tags) {
    // TODO check that tokens follow each other within the same sentence
    // for now presume that $token_ids[0] is the starting token
    if (!check_ne_paragraph_status($annot_id, $_SESSION['user_id']))
        throw new Exception();

    sql_begin();
    sql_pe("
        INSERT INTO ne_entities
        (annot_id, start_token, length, updated_ts)
        VALUES (?, ?, ?, ?)
    ", array($annot_id, $token_ids[0], sizeof($token_ids), time()));

    $entity_id = sql_insert_id();
    set_ne_tags($entity_id, $tags, $annot_id);
    sql_commit();
    return $entity_id;
}

function delete_ne_entity($entity_id, $annot_id=0) {
    if (!$annot_id) {
        $res = sql_pe("SELECT annot_id FROM ne_entities WHERE entity_id = ?", array($entity_id));
        if (empty($res)) return FALSE;

        $annot_id = $res[0]['annot_id'];
    }

    $res = sql_pe("SELECT mention_id FROM ne_entities_mentions WHERE entity_id=? LIMIT 1", array($entity_id));
    if (!empty($res) && $res[0]['mention_id'] > 0)
        throw new Exception("Cannot delete entity in mention");

    if (!check_ne_paragraph_status($annot_id, $_SESSION['user_id']))
        throw new Exception();

    sql_begin();
    sql_pe("DELETE FROM ne_entity_tags WHERE entity_id=?", array($entity_id));
    sql_pe("DELETE FROM ne_entities WHERE entity_id=?", array($entity_id));
    sql_commit();
}

function set_ne_tags($entity_id, $tags, $annot_id=0) {
    // overwrites old set of tags
    // TODO check that tags and annotation belong to the same tagset
    if (!$annot_id) {
        $res = sql_pe("SELECT annot_id FROM ne_entities WHERE entity_id = ?", array($entity_id));
        $annot_id = $res[0]['annot_id'];
    }

    if (!check_ne_paragraph_status($annot_id, $_SESSION['user_id']))
        throw new Exception();

    sql_begin();
    sql_pe("DELETE FROM ne_entity_tags WHERE entity_id = ?", array($entity_id));
    $res = sql_prepare("INSERT INTO ne_entity_tags VALUES(?, ?)");
    foreach ($tags as $tag)
        sql_execute($res, array($entity_id, $tag));

    sql_commit();
}

function log_event($message) {
    return sql_pe("INSERT INTO ne_event_log (user_id, message)
            VALUES (?, ?)", array($_SESSION['user_id'], $message));
}

function add_mention($entity_ids, $object_type) {
    $entities_in = str_repeat('?,', count($entity_ids) - 1) . '?';
    $entities = sql_pe("SELECT entity_id FROM ne_entities WHERE entity_id IN (" .$entities_in . ")", $entity_ids);
    if (sizeof($entities) != sizeof($entity_ids))
        throw new Exception("Not valid NE entity ids");
    $type = sql_pe("SELECT * FROM ne_object_types WHERE object_type_id = ? LIMIT 1", array($object_type));
    if (sizeof($type) != 1)
        throw new Exception("Not valid object type");

    sql_begin();
    sql_pe("INSERT INTO ne_mentions SET object_type_id = ?", array($object_type));
    $mention_id = sql_insert_id();
    $vals = array();
    foreach ($entities as $ent)
        $vals[] = "(" . $ent["entity_id"] . ", " . $mention_id . ")";
    sql_query("INSERT INTO ne_entities_mentions VALUES " . implode(", ", $vals));
    sql_commit();
    return $mention_id;
}

function delete_mention($mention_id) {
    sql_begin();
    sql_pe("DELETE FROM ne_entities_mentions WHERE mention_id = ?", array($mention_id));
    sql_pe("DELETE FROM ne_mentions WHERE mention_id = ?", array($mention_id));
    sql_commit();
}

function update_mention($mention_id, $object_type) {
    $mention = sql_pe("SELECT * FROM ne_mentions WHERE mention_id = ? LIMIT 1", array($mention_id));
    if (sizeof($mention) != 1)
        throw new Exception("Mention not found");
    $type = sql_pe("SELECT * FROM ne_object_types WHERE object_type_id = ? LIMIT 1", array($object_type));
    if (sizeof($type) != 1)
        throw new Exception("Not valid object type");
    sql_pe("UPDATE ne_mentions SET object_type_id = ? WHERE mention_id = ? LIMIT 1", array($object_type, $mention_id));
}

function delete_entity_mention_link($entity_id, $mention_id) {
    $entity = sql_pe("SELECT * FROM ne_entities WHERE entity_id = ? LIMIT 1", array($entity_id));
    if (sizeof($entity) != 1)
        throw new Exception("Entity not found");
    $mention = sql_pe("SELECT * FROM ne_mentions WHERE mention_id = ? LIMIT 1", array($mention_id));
    if (sizeof($mention) != 1)
        throw new Exception("Mention not found");
    sql_pe("DELETE FROM ne_entities_mentions WHERE entity_id = ? AND mention_id = ?", array($entity_id, $mention_id));
}
