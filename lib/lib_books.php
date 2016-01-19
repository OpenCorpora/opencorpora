<?php
require_once('lib_dict.php');
require_once('lib_annot.php');
require_once('lib_ne.php');
require_once('constants.php');

function get_books_list() {
    $res = sql_query("SELECT `book_id`, `book_name` FROM `books` WHERE `parent_id`=0 ORDER BY `book_name`");
    $out = array('num' => sql_num_rows($res));
    while ($r = sql_fetch_array($res)) {
       $out['list'][] = array('id' => $r['book_id'], 'title' => $r['book_name']);
    }
    return $out;
}
function get_book_parents($book_id, $include_self=false) {
    // returned array starts with the closest ancestor
    $parents = array();
    if ($include_self) {
        $res = sql_pe("SELECT book_id, book_name FROM books WHERE book_id=? LIMIT 1", array($book_id));
        $parents[] = array('id' => $res[0]['book_id'], 'title' => $res[0]['book_name']);
    }
    $tid = $book_id;
    $res = sql_prepare("SELECT book_id, book_name FROM books WHERE book_id=(SELECT parent_id FROM books WHERE book_id=? LIMIT 1) AND book_id>0 LIMIT 1");
    while ($tid) {
        sql_execute($res, array($tid));
        $r = sql_fetch_array($res);
        if ($r) {
            $parents[] = array('id' => $r['book_id'], 'title' => $r['book_name']);
            $tid = $r['book_id'];
        } else
            break;
    }
    return $parents;
}
function check_book_hidden($book_id) {
    global $config;
    // hide books when 24 hours passed after last edit
    $res = sql_pe("
        SELECT MAX(timestamp) AS ts
        FROM tokens
        LEFT JOIN sentences USING (sent_id)
        LEFT JOIN paragraphs USING (par_id)
        LEFT JOIN tf_revisions USING (tf_id)
        LEFT JOIN rev_sets USING (set_id)
        WHERE book_id = ?
    ", array($book_id));
    if (!$res[0]['ts'])
        return;
    $last_edit = $res[0]['ts'];
    if (!user_has_permission(PERM_CHECK_TOKENS) && $book_id >= $config['misc']['hidden_books_start_id'] && (time() - $last_edit > SEC_PER_DAY * 7))
        throw new Exception("Sorry, this book is temporarily hidden");
}
function get_book_page($book_id, $full = false, $override_hidden = false) {
    if (!$override_hidden)
        check_book_hidden($book_id);
    $res = sql_pe("SELECT * FROM `books` WHERE `book_id`=? LIMIT 1", array($book_id));
    if (!sizeof($res))
        throw new UnexpectedValueException();
    $out = array (
        'id'     => $book_id,
        'title'  => $res[0]['book_name'],
        'select' => get_books_for_select(),
        'is_wikinews' => $res[0]['parent_id'] == 56,
        'is_chaskor_news' => $res[0]['parent_id'] == 226
    );
    get_book_tags($book_id, $out);
    //sub-books
    foreach (sql_pe("SELECT book_id, book_name FROM books WHERE parent_id=? ORDER BY book_name", array($book_id)) as $r) {
        $out['children'][] = array('id' => $r['book_id'], 'title' => $r['book_name']);
    }
    //parents
    $out['parents'] = array_reverse(get_book_parents($book_id));
    //sentences
    if ($full) {
        $q = "SELECT p.`pos` ppos, par_id, s.sent_id, s.`pos` spos";
        if (user_has_permission(PERM_ADDER)) $q .= ", ss.status";
        $q .= "\nFROM paragraphs p
            LEFT JOIN sentences s
            USING (par_id)\n";

        if (user_has_permission(PERM_ADDER)) $q .= "LEFT JOIN sentence_check ss ON (s.sent_id = ss.sent_id AND ss.status=1 AND ss.user_id=".$_SESSION['user_id'].")\n";
        $q .= "WHERE p.book_id = ?
            ORDER BY p.`pos`, s.`pos`";
        $res = sql_pe($q, array($book_id));
        $res1 = sql_prepare("SELECT tf_id, tf_text FROM tokens WHERE sent_id=? ORDER BY pos");
        foreach ($res as $r) {
            sql_execute($res1, array($r['sent_id']));
            $tokens = array();
            while ($r1 = sql_fetch_array($res1)) {
                $tokens[] = array('text' => $r1['tf_text'], 'id' => $r1['tf_id']);
            }
            $new_a = array('id' => $r['sent_id'], 'pos' => $r['spos'], 'tokens' => $tokens);
            if (user_has_permission(PERM_ADDER))
                $new_a['checked'] = $r['status'];
            $out['paragraphs'][$r['ppos']]['sentences'][] = $new_a;
            $out['paragraphs'][$r['ppos']]['id'] = $r['par_id'];
        }
    } else {
        $res = sql_pe("SELECT p.`pos` ppos, s.sent_id, s.`pos` spos FROM paragraphs p LEFT JOIN sentences s ON (p.par_id = s.par_id) WHERE p.book_id = ? ORDER BY p.`pos`, s.`pos`", array($book_id));
        foreach ($res as $r) {
            $r1 = sql_fetch_array(sql_query("SELECT source, SUBSTRING_INDEX(source, ' ', 6) AS `cnt` FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1"));
            if ($r1['source'] === $r1['cnt']) {
                $out['paragraphs'][$r['ppos']]['sentences'][] = array('pos' => $r['spos'], 'id' => $r['sent_id'], 'snippet' => $r1['source']);
                continue;
            }

            $snippet = '';

            $r1 = sql_fetch_array(sql_query("SELECT SUBSTRING_INDEX(source, ' ', 3) AS `start` FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1"));
            $snippet = $r1['start'];

            if ($snippet) $snippet .= '... ';

            $r1 = sql_fetch_array(sql_query("SELECT SUBSTRING_INDEX(source, ' ', -3) AS `end` FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1"));
            $snippet .= $r1['end'];

            $out['paragraphs'][$r['ppos']]['sentences'][] = array('pos' => $r['spos'], 'id' => $r['sent_id'], 'snippet' => $snippet);
        }
    }
    return $out;
}
function get_book_first_sentence_id($book_id) {
    $res = sql_query("
        SELECT sent_id
        FROM sentences s
            JOIN paragraphs p USING (par_id)
        WHERE book_id = $book_id
        ORDER BY p.pos, s.pos
        LIMIT 1
    ");
    if (sql_num_rows($res) == 0)
        return 0;
    $r = sql_fetch_array($res);
    return $r['sent_id'];
}
function books_add($name, $parent_id=0) {
    check_permission(PERM_ADDER);
    if ($name === '')
        throw new UnexpectedValueException();
    sql_pe("INSERT INTO `books` VALUES(NULL, ?, ?, 0, 0)", array($name, $parent_id));
    return sql_insert_id();
}
function books_move($book_id, $to_id) {
    if ($book_id == $to_id)
        throw new UnexpectedValueException();
    check_permission(PERM_ADMIN);

    //to avoid loops
    $tid = $to_id;
    $res = sql_prepare("SELECT parent_id FROM books WHERE book_id=? AND parent_id>0 LIMIT 1");
    while ($tid) {
        sql_execute($res, array($tid));
        $r = sql_fetch_array($res);
        if ($r) {
            $tid = $r['parent_id'];
            if ($tid == $book_id) {
                throw new UnexpectedValueException("Error: setting looping parent");
                break;
            }
        } else
            break;
    }

    sql_pe("UPDATE `books` SET parent_id=? WHERE book_id=? LIMIT 1", array($to_id, $book_id));
}
function books_rename($book_id, $name) {
    check_permission(PERM_ADDER);
    if ($name === '')
        throw new UnexpectedValueException();
    sql_pe("UPDATE `books` SET book_name=? WHERE book_id=? LIMIT 1", array($name, $book_id));
}
function get_books_for_select($parent = -1) {
    $out = array();
    $pg = $parent > -1 ? "WHERE `parent_id`=$parent" : '';
    $res = sql_query("SELECT `book_id`, `book_name` FROM `books` ".$pg." ORDER BY `book_name`", 0);
    while ($r = sql_fetch_array($res)) {
        $out["$r[book_id]"] = $r['book_name'];
    }
    return $out;
}
function get_book_tags($book_id, &$out) {
    $res = sql_pe("SELECT tag_name FROM book_tags WHERE book_id=?", array($book_id));
    $url_res = sql_prepare("SELECT filename FROM downloaded_urls WHERE url=? LIMIT 1");
    $tags = array();
    foreach ($res as $r) {
        if (preg_match('/^(.+?)\:(.+)$/', $r['tag_name'], $matches)) {
            $ar = array('prefix' => $matches[1], 'body' => $matches[2], 'full' => $r['tag_name']);
            if ($matches[1] == 'url') {
                sql_execute($url_res, array(htmlspecialchars_decode($matches[2])));
                if ($r1 = sql_fetch_array($url_res)) {
                    $ar['filename'] = $r1['filename'];
                }
                if (preg_match('/^http:\/\/ru.wikinews.org\/wiki\/(.+)$/', $matches[2], $wn_matches)) {
                    $out['wikinews_title'] = str_replace('_', ' ', $wn_matches[1]);
                }
                elseif (preg_match('/^http:\/\/(?:www\.)?chaskor\.ru\/news\/(.+)$/', $matches[2], $wn_matches)) {
                    $out['chaskor_news_title'] = $wn_matches[1];
                }
            }
            $tags[] = $ar;
        } else
            $tags[] = array('prefix' => '', 'body' => $r['tag_name'], 'full' => $r['tag_name']);
    }
    $url_res->closeCursor();
    $out['tags'] = $tags;
}
function books_add_tag($book_id, $tag_name) {
    check_permission(PERM_ADDER);
    $tag_name = preg_replace('/\:\s+/', ':', trim($tag_name), 1);
    sql_begin();
    books_del_tag($book_id, $tag_name);
    sql_pe("INSERT INTO `book_tags` VALUES(?, ?)", array($book_id, $tag_name));
    sql_commit();
}
function books_del_tag($book_id, $tag_name) {
    check_permission(PERM_ADDER);
    if (!$book_id || !$tag_name)
        throw new UnexpectedValueException();
    sql_pe("DELETE FROM `book_tags` WHERE book_id=? AND tag_name=?", array($book_id, $tag_name));
}
function download_url($url, $force=false) {
    global $config;

    if (!$url)
        throw new UnexpectedValueException();
    
    //check if it has been already downloaded
    sql_begin();
    $res = sql_pe("SELECT url FROM downloaded_urls WHERE url=? LIMIT 1", array($url));
    if (sizeof($res) > 0) {
        if ($force)
            sql_pe("DELETE FROM downloaded_urls WHERE url=?", array($url));
        else
            throw new Exception();
    }

    // preprocess url in case it has non-ASCII symbols
    $host = parse_url($url, PHP_URL_HOST);
    $better_host = idn_to_ascii($host);
    $better_url = str_replace($host, $better_host, $url);

    //downloading
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $better_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'OpenCorpora.org bot');
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $contents = curl_exec($ch);
    curl_close($ch);

    //writing to disk
    $filename = uniqid('', 1);
    $res = file_put_contents($config['project']['root'] . "/files/saved/$filename.html", $contents);
    if (!$res)
        throw new Exception();

    sql_pe("INSERT INTO downloaded_urls VALUES(?, ?)", array($url, $filename));
    sql_commit();
    return $filename;
}
function split_paragraph($sentence_id) {
    // fails if this paragraph has any NE

    if (!$sentence_id)
        throw new UnexpectedValueException();
    check_permission(PERM_ADDER);
    //get pos
    $res = sql_pe("SELECT pos FROM sentences WHERE sent_id=? LIMIT 1", array($sentence_id));
    $spos = $res[0]['pos'];
    //get the paragraph info
    $res = sql_pe("SELECT par_id, book_id, pos FROM paragraphs WHERE par_id=(SELECT par_id FROM sentences WHERE sent_id=? LIMIT 1) LIMIT 1", array($sentence_id));
    $r = $res[0];

    // check for NE
    $res = sql_pe("SELECT annot_id FROM ne_paragraphs WHERE par_id = ? LIMIT 1", array($r['par_id']));
    if (sizeof($res) > 0)
        throw new Exception("This paragraph cannot be split (NE)");

    sql_begin();
    //move the following paragraphs
    sql_pe("UPDATE paragraphs SET pos=pos+1 WHERE book_id=? AND pos > ?", array($r['book_id'], $r['pos']));
    //make a new paragraph
    sql_pe("INSERT INTO paragraphs VALUES (NULL, ?, ?)", array($r['book_id'], $r['pos'] + 1));
    $new_par_id = sql_insert_id();
    //move the following sentences to the new paragraph
    sql_pe(
        "UPDATE sentences SET par_id=?, pos=pos-$spos WHERE par_id=? AND pos > ?",
        array($new_par_id, $r['par_id'], $spos)
    );
    sql_commit();
    return $r['book_id'];
}
function merge_paragraphs($par_id) {
    // merges this paragraph to the previous one
    if (!$par_id)
        throw new UnexpectedValueException();
    check_permission(PERM_ADDER);

    $res = sql_pe("SELECT book_id, pos FROM paragraphs WHERE par_id = ?", array($par_id));
    $pos = $res[0]['pos'];
    $book_id = $res[0]['book_id'];

    $res = sql_pe("
        SELECT par_id
        FROM paragraphs
        WHERE book_id = ?
        AND pos < ?
        ORDER BY pos DESC
        LIMIT 1
    ", array($book_id, $pos));
    if (!sizeof($res))
        throw new Exception("No previous paragraph");

    $prev_id = $res[0]['par_id'];

    $res = sql_pe("
        SELECT MAX(pos) AS maxpos
        FROM sentences
        WHERE par_id = ?
    ", array($prev_id));
    $maxpos = $res[0]['maxpos'];

    // move sentences
    sql_pe("UPDATE sentences SET par_id = ?, pos=pos+$maxpos WHERE par_id = ?",
        array($prev_id, $par_id));

    // change NE accordingly
    sql_pe("UPDATE ne_paragraphs SET par_id = ? WHERE par_id = ?", array($prev_id, $par_id));

    // delete paragraph
    sql_pe("DELETE FROM paragraphs WHERE par_id = ? LIMIT 1", array($par_id));

    $res = sql_pe("SELECT sent_id FROM sentences WHERE par_id = ? ORDER BY pos LIMIT 1", array($prev_id));

    return array($book_id, $res[0]['sent_id']);
}
function sentence_has_ne_markup($sent_id) {
    return sizeof(get_all_ne_by_sentence($sent_id)) > 0;
}
function sentence_has_syntax_markup($sent_id) {
    $res = sql_pe("SELECT parse_id FROM syntax_parses WHERE sent_id = ? LIMIT 1", array($sent_id));
    return sizeof($res) > 0;
}
function is_token_covered_by_ne_markup($tf_id) {
    $res = sql_pe("SELECT sent_id FROM tokens WHERE tf_id=? LIMIT 1", array($tf_id));
    $entities = get_all_ne_by_sentence($res[0]['sent_id']);

    $tokres = sql_prepare("
        SELECT tf_id
        FROM tokens
        WHERE sent_id = ?
        AND pos >= (
            SELECT pos FROM tokens WHERE tf_id = ?
        )
        ORDER BY pos
        LIMIT ?
    ");
    foreach ($entities as $e) {
        sql_execute($tokres, array($sent_id, $e['start_token'], $e['length']));
        foreach (sql_fetchall($tokres) as $r) {
            if ($r['tf_id'] == $tf_id)
                return true;
        }
    }
    return false;
}
function split_sentence($token_id) {
    // note: comments will stay with the first sentence
    // note: fails if this sentence has NE or syntax markup

    check_permission(PERM_ADDER);
    //find which sentence the token is in
    $res = sql_pe("SELECT sent_id, pos FROM tokens WHERE tf_id=? LIMIT 1", array($token_id));
    $r = $res[0];
    $sent_id = $r['sent_id'];
    $tpos = $r['pos'];
    
    if (sentence_has_ne_markup($sent_id))
        throw new Exception("This sentence cannot be split (NE)");

    if (sentence_has_syntax_markup($sent_id))
        throw new Exception("This sentence cannot be split (syntax)");

    //check that it is not the last token
    $r = sql_fetch_array(sql_query("SELECT MAX(pos) mpos FROM tokens WHERE sent_id=$sent_id"));
    if ($r['mpos'] == $tpos)
        throw new Exception();
    //split the source field
    $r = sql_fetch_array(sql_query("SELECT source, pos, par_id FROM sentences WHERE sent_id=$sent_id LIMIT 1"));
    $source = $r['source'];
    $spos = $r['pos'];
    $par_id = $r['par_id'];
    $res = sql_query("SELECT tf_text FROM tokens WHERE sent_id=$sent_id AND pos<=$tpos ORDER BY pos");
    $t = 0;
    while ($r = sql_fetch_array($res)) {
       while (mb_substr($source, $t, mb_strlen($r['tf_text'], 'UTF-8'), 'UTF-8') !== $r['tf_text']) {
           $t++;
           if ($t > mb_strlen($source, 'UTF-8'))
               throw new Exception();
       }
       $t += mb_strlen($r['tf_text'], 'UTF-8');
    }
    $source_left = trim(mb_substr($source, 0, $t, 'UTF-8'));
    $source_right = trim(mb_substr($source, $t, mb_strlen($source, 'UTF-8')-1, 'UTF-8'));
    sql_begin();
    //shift the following sentences
    sql_query("UPDATE sentences SET pos=pos+1 WHERE par_id=$par_id AND pos > $spos");
    //create new sentence
    sql_pe("INSERT INTO sentences VALUES(NULL, ?, ?, ?, 0)", array($par_id, $spos+1, $source_right));
    $new_sent_id = sql_insert_id();
    //move tokens
    sql_query("UPDATE tokens SET sent_id=$new_sent_id, pos=pos-$tpos WHERE sent_id=$sent_id AND pos>$tpos");
    //change source in the original sentence
    sql_pe("UPDATE sentences SET check_status=0, source=? WHERE sent_id=? LIMIT 1", array($source_left, $sent_id));
    //drop status
    sql_query("DELETE FROM sentence_check WHERE sent_id=$sent_id");
    //delete from strange splitting
    sql_query("DELETE FROM sentences_strange WHERE sent_id=$sent_id LIMIT 1");

    sql_commit();
    $r = sql_fetch_array(sql_query("SELECT book_id FROM paragraphs WHERE par_id=$par_id LIMIT 1"));
    return array($r['book_id'], $sent_id);
}
function merge_sentences($id1, $id2) {
    check_permission(PERM_ADDER);
    if ($id1 < 1 || $id2 < 1)
        throw new UnexpectedValueException();
    // check same paragraph and adjacency
    $res = sql_pe("SELECT pos, par_id FROM sentences WHERE sent_id IN (?, ?) ORDER BY pos LIMIT 2", array($id1, $id2));
    $r1 = $res[0];
    $r2 = $res[1];
    $res = sql_query("SELECT pos FROM sentences WHERE par_id = ".$r1['par_id']." AND pos > ".$r1['pos']." AND pos < ".$r2['pos']." LIMIT 1");
    if ($r1['par_id'] != $r2['par_id'] || sql_num_rows($res) > 0) {
        throw new Exception();
    }
    //moving tokens
    sql_begin();
    $res = sql_pe("SELECT MAX(pos) AS maxpos FROM tokens WHERE sent_id=?", array($id1));
    sql_pe(
        "UPDATE tokens SET sent_id=?, pos=pos+? WHERE sent_id=?",
        array($id1, $res[0]['maxpos'], $id2)
    );
    //merging source text
    $res_src = sql_prepare("SELECT `source` FROM sentences WHERE sent_id=? LIMIT 1");
    sql_execute($res_src, array($id1));
    $r1 = sql_fetchall($res_src);
    sql_execute($res_src, array($id2));
    $r2 = sql_fetchall($res_src);
    sql_pe(
        "UPDATE sentences SET source=? WHERE sent_id=? LIMIT 1",
        array($r1[0]['source'] . ' ' . $r2[0]['source'], $id1)
    );
    //dropping status, moving comments
    sql_pe("UPDATE sentences SET check_status=0 WHERE sent_id=? LIMIT 1", array($id1));
    sql_pe("UPDATE sentence_comments SET sent_id=? WHERE sent_id=?", array($id1, $id2));
    sql_pe("DELETE FROM sentence_check WHERE sent_id=? OR sent_id=?", array($id1, $id2));

    // change syntax markup accordingly
    sql_pe("UPDATE syntax_parses SET sent_id = ? WHERE sent_id = ?", array($id1, $id2));

    //deleting sentence
    sql_pe("DELETE FROM sentence_authors WHERE sent_id=? LIMIT 1", array($id2));
    sql_pe("DELETE FROM sentences WHERE sent_id=? LIMIT 1", array($id2));
    sql_commit();
}
function delete_sentence($sid) {
    // fails if this sentence has NE or syntax markup
    check_permission(PERM_ADMIN);

    if (sentence_has_ne_markup($sid))
        throw new Exception("This sentence cannot be deleted (NE)");
    if (sentence_has_syntax_markup($sid))
        throw new Exception("This sentence cannot be deleted (syntax)");

    sql_begin();
    sql_pe("DELETE FROM sentence_authors WHERE sent_id=? LIMIT 1", array($sid));
    sql_pe("DELETE FROM sentence_check WHERE sent_id=?", array($sid));
    sql_pe("DELETE FROM sentence_comments WHERE sent_id=?", array($sid));

    foreach (sql_pe("SELECT tf_id FROM tokens WHERE sent_id=?", array($sid)) as $r)
        delete_token($r['tf_id']);

    $res = sql_pe("SELECT par_id FROM sentences WHERE sent_id=? LIMIT 1", array($sid));
    $par_id = $res[0]['par_id'];

    sql_pe("DELETE FROM sentences WHERE sent_id=? LIMIT 1", array($sid));
    
    // delete paragraph if it was the last sentence
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt FROM sentences WHERE par_id=$par_id"));
    if ($r['cnt'] == 0)
        sql_query("DELETE FROM paragraphs WHERE par_id=$par_id LIMIT 1");
    sql_commit();
}
function delete_paragraph($pid) {
    check_permission(PERM_ADMIN);
    $res = sql_pe("SELECT sent_id FROM sentences WHERE par_id=?", array($pid));
    sql_begin();
    foreach ($res as $sent)
        delete_sentence($sent['sent_id']);
    sql_commit();
}
function save_token_text($tf_id, $tf_text) {
    $tf_text = trim($tf_text);
    if (!$tf_id || !$tf_text)
        throw new UnexpectedValueException();

    sql_begin();
    $revset_id = create_revset("Change token #$tf_id text to <$tf_text>");
    $token_for_form2tf = str_replace('ё', 'е', mb_strtolower($tf_text));
    sql_pe("UPDATE tokens SET tf_text = ? WHERE tf_id=? LIMIT 1", array($tf_text, $tf_id));
    sql_pe("DELETE FROM form2tf WHERE tf_id=?", array($tf_id));
    sql_pe("INSERT INTO form2tf VALUES(?, ?)", array($token_for_form2tf, $tf_id));
    $parse = new MorphParseSet(false, $tf_text);
    create_tf_revision($revset_id, $tf_id, $parse->to_xml());

    sql_commit();
}
function delete_token($tf_id, $delete_history=true) {
    if (is_token_covered_by_ne_markup($tf_id))
        throw new Exception("Cannot delete token under NE markup");

    $sample_ids = array(0);
    $res = sql_query("SELECT sample_id FROM morph_annot_samples WHERE tf_id = $tf_id");
    while ($r = sql_fetch_array($res))
        $sample_ids[] = $r['sample_id'];
    $sids = join(',', $sample_ids);
    sql_begin();

    sql_query("DELETE FROM form2tf WHERE tf_id = $tf_id");
    if ($delete_history)
        sql_query("DELETE FROM tf_revisions WHERE tf_id = $tf_id");
    sql_query("DELETE FROM morph_annot_candidate_samples WHERE tf_id = $tf_id");
    sql_query("DELETE FROM morph_annot_moderated_samples WHERE sample_id IN ($sids)");
    sql_query("DELETE FROM morph_annot_instances WHERE sample_id IN ($sids)");
    sql_query("DELETE FROM morph_annot_rejected_samples WHERE sample_id IN ($sids)");
    sql_query("DELETE FROM morph_annot_comments WHERE sample_id IN ($sids)");
    sql_query("DELETE FROM morph_annot_click_log WHERE sample_id IN ($sids)");
    sql_query("DELETE FROM morph_annot_samples WHERE tf_id = $tf_id");
    sql_query("DELETE FROM updated_tokens WHERE token_id = $tf_id");
    sql_query("DELETE FROM tokens WHERE tf_id = $tf_id LIMIT 1");

    sql_commit();
}
function merge_tokens_ii($id_array) {
    //ii stands for "id insensitive"
    if (sizeof($id_array) < 2)
        throw new UnexpectedValueException();
    check_permission(PERM_ADDER);

    $id_array = array_map('intval', $id_array);
    foreach ($id_array as $tid) {
        if (is_token_covered_by_ne_markup($tid))
            throw new Exception("Cannot change tokens under NE markup");
    }
    $joined = join(',', $id_array);

    //check if they are all in the same sentence
    $res = sql_query("SELECT distinct sent_id FROM tokens WHERE tf_id IN($joined)");
    if (sql_num_rows($res) > 1)
        throw new Exception();

    $r = sql_fetch_array($res);
    $sent_id = $r['sent_id'];
    //check if they all stand in a row
    $r = sql_fetch_array(sql_query("SELECT MIN(pos) AS minpos, MAX(pos) AS maxpos FROM tokens WHERE tf_id IN($joined)"));
    $res = sql_query("SELECT tf_id FROM tokens WHERE sent_id=$sent_id AND pos > ".$r['minpos']." AND pos < ".$r['maxpos']." AND tf_id NOT IN ($joined) LIMIT 1");
    if (sql_num_rows($res) > 0)
        throw new Exception();

    //assemble new token, delete others from form2tf and tokens, update tf_id in their revisions
    $res = sql_query("SELECT tf_id, tf_text FROM tokens WHERE tf_id IN ($joined) ORDER BY pos");
    $r = sql_fetch_array($res);
    $new_id = $r['tf_id'];
    $new_text = $r['tf_text'];
    sql_begin();
    while ($r = sql_fetch_array($res)) {
        $new_text .= $r['tf_text'];
        sql_query("UPDATE tf_revisions SET tf_id=$new_id WHERE tf_id=".$r['tf_id']);
        delete_token($r['tf_id'], false);
    }
    //update tf_text, add new revision
    $revset_id = create_revset("Tokens $joined merged to <$new_text>");
    $token_for_form2tf = str_replace('ё', 'е', mb_strtolower($new_text));
    sql_pe("UPDATE tokens SET tf_text = ? WHERE tf_id=? LIMIT 1", array($new_text, $new_id));
    sql_pe("INSERT INTO form2tf VALUES(?, ?)", array($token_for_form2tf, $new_id));
    $parse = new MorphParseSet(false, $new_text);
    create_tf_revision($revset_id, $new_id, $parse->to_xml());
    //drop sentence status
    sql_query("UPDATE sentences SET check_status='0' WHERE sent_id=$sent_id LIMIT 1");
    sql_query("DELETE FROM sentence_check WHERE sent_id=$sent_id");
    sql_commit();
}
function split_token($token_id, $num) {
    //$num is the number of characters (in the beginning) that should become a separate token
    check_permission(PERM_ADDER);

    if (is_token_covered_by_ne_markup($token_id))
        throw new Exception("Cannot split token under NE markup");

    if (!$token_id || !$num)
        throw new UnexpectedValueException();
    $res = sql_pe("SELECT tf_text, sent_id, pos FROM tokens WHERE tf_id=? LIMIT 1", array($token_id));
    if (sizeof($res) == 0) {
        throw new Exception();
    }
    $r = $res[0];
    $text1 = trim(mb_substr($r['tf_text'], 0, $num));
    $text2 = trim(mb_substr($r['tf_text'], $num));
    if (!$text1 || !$text2) {
        throw new Exception();
    }
    sql_begin();
    //create revset
    $revset_id = create_revset("Token $token_id (<".$r['tf_text'].">) split to <$text1> and <$text2>");
    $token_for_form2tf = str_replace('ё', 'е', mb_strtolower($text1));
    //update other tokens in the sentence
    sql_query("UPDATE tokens SET pos=pos+1 WHERE sent_id = ".$r['sent_id']." AND pos > ".$r['pos']);
    //create new token and parse
    sql_pe(
        "INSERT INTO tokens VALUES(NULL, ?, ?, ?)",
        array($r['sent_id'], $r['pos']+1, $text2)
    );
    $parse1 = new MorphParseSet(false, $text1);
    $parse2 = new MorphParseSet(false, $text2);
    create_tf_revision($revset_id, sql_insert_id(), $parse2->to_xml());
    //update old token and parse
    sql_pe("DELETE FROM form2tf WHERE tf_id=?", array($token_id));
    sql_pe("UPDATE tokens SET tf_text=? WHERE tf_id=? LIMIT 1", array($text1, $token_id));
    sql_pe("INSERT INTO form2tf VALUES(?, ?)", array($token_for_form2tf, $token_id));
    create_tf_revision($revset_id, $token_id, $parse1->to_xml());

    //dropping sentence status
    $res = sql_pe("SELECT sent_id FROM tokens WHERE tf_id=? LIMIT 1", array($token_id));
    $sent_id = $res[0]['sent_id'];

    sql_query("UPDATE sentences SET check_status='0' WHERE sent_id=$sent_id LIMIT 1");
    sql_query("DELETE FROM sentence_check WHERE sent_id=$sent_id");

    sql_commit();
    
    $res = sql_query("SELECT book_id FROM paragraphs WHERE par_id = (SELECT par_id FROM sentences WHERE sent_id=$sent_id LIMIT 1)");
    $r = sql_fetch_array($res);

    return array($r['book_id'], $sent_id);
}

// book adding queue

function get_sources_page($skip = 0, $show_type = '', $src = 0) {
    check_permission(PERM_ADDER);
    $out = array();
    $q_main = "SELECT s.source_id, s.url, s.title, s.user_id, s.book_id, u.user_shown_name AS user_name, b.book_name FROM sources s LEFT JOIN books b ON (s.book_id = b.book_id) LEFT JOIN users u ON (s.user_id = u.user_id) ";
    $q_tail = '';
    $q_cnt = "SELECT COUNT(*) AS cnt FROM sources s ";
    if ($show_type == 'my')
        $q_tail = "WHERE s.user_id = ".$_SESSION['user_id'];
    elseif ($show_type == 'active')
        $q_tail = "WHERE s.user_id > 0 OR s.book_id > 0";
    elseif ($show_type == 'free')
        $q_tail = "WHERE (s.parent_id=$src OR s.parent_id IN (SELECT source_id FROM sources WHERE parent_id=$src)) AND s.user_id = 0";
    $q_tail2 = $show_type == 'free' ? " ORDER BY RAND() LIMIT 200" : " ORDER BY s.book_id DESC, s.source_id LIMIT $skip,200";
    $r = sql_fetch_array(sql_query($q_cnt.$q_tail));
    $out['total'] = $r['cnt'];
    $res = sql_query($q_main.$q_tail.$q_tail2);
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT `user_id`, `status`, `timestamp` FROM sources_status WHERE source_id=".$r['source_id']." ORDER BY `timestamp` DESC LIMIT 1"));
        $comments = array();
        $res1 = sql_query("SELECT user_shown_name AS user_name, text, timestamp FROM sources_comments sc LEFT JOIN users u ON (sc.user_id=u.user_id) WHERE sc.source_id=".$r['source_id']." ORDER BY comment_id");
        while ($r2 = sql_fetch_array($res1)) {
            $comments[] = array('username' => $r2['user_name'], 'timestamp' => $r2['timestamp'], 'text' => $r2['text']);
        }
        $out['src'][] = array(
            'id' => $r['source_id'],
            'url' => $r['url'],
            'title' => $r['title'],
            'user_id' => $r['user_id'],
            'user_name' => $r['user_name'],
            'book_id' => $r['book_id'],
            'book_title' => $r['book_name'],
            'status' => $r1['status'],
            'status_changer' => $r1['user_id'],
            'status_ts' => $r1['timestamp'],
            'comments' => $comments
        );
    }
    return $out;
}
function source_add($url, $title, $parent_id) {
    check_permission(PERM_ADDER);
    if (!$url)
        throw new UnexpectedValueException();
    
    sql_pe("INSERT INTO sources VALUES(NULL, ?, ?, ?, 0, 0)", array($parent_id, $url, $title));
}

?>
