<?php
require_once('lib_dict.php');
function get_books_list() {
    $res = sql_query("SELECT `book_id`, `book_name` FROM `books` WHERE `parent_id`=0 ORDER BY `book_name`");
    $out = array('num' => sql_num_rows($res));
    while ($r = sql_fetch_array($res)) {
       $out['list'][] = array('id' => $r['book_id'], 'title' => $r['book_name']);
    }
    return $out;
}
function get_book_page($book_id, $full = false) {
    $r = sql_fetch_array(sql_query("SELECT * FROM `books` WHERE `book_id`=$book_id LIMIT 1"));
    $out = array (
        'id'     => $book_id,
        'title'  => $r['book_name'],
        'select' => get_books_for_select()
    );
    //tags
    $res = sql_query("SELECT tag_name FROM book_tags WHERE book_id=$book_id");
    while ($r = sql_fetch_array($res)) {
        if (preg_match('/^(.+?)\:(.+)$/', $r['tag_name'], $matches)) {
            $ar = array('prefix' => $matches[1], 'body' => $matches[2], 'full' => $r['tag_name']);
            if ($matches[1] == 'url') {
                $res1 = sql_query("SELECT filename FROM downloaded_urls WHERE url='".mysql_real_escape_string($matches[2])."' LIMIT 1");
                if ($r1 = sql_fetch_array($res1)) {
                    $ar['filename'] = $r1['filename'];
                }
            }
            $out['tags'][] = $ar;
        } else
            $out['tags'][] = array('prefix' => '', 'body' => $r['tag_name'], 'full' => $r['tag_name']);
    }
    //sub-books
    $res = sql_query("SELECT book_id, book_name FROM books WHERE parent_id=$book_id ORDER BY book_name");
    while($r = sql_fetch_array($res)) {
        $out['children'][] = array('id' => $r['book_id'], 'title' => $r['book_name']);
    }
    //parents
    $res = sql_query("SELECT book_id, book_name FROM books WHERE book_id=(SELECT parent_id FROM books WHERE book_id=$book_id LIMIT 1) AND book_id>0 LIMIT 1");
    if (sql_num_rows($res) > 0) {
        $r = sql_fetch_array($res);
        $out['parents'] = array(array('id' => $r['book_id'], 'title' => $r['book_name']));
    }
    //sentences
    if ($full) {
        $q = "SELECT p.`pos` ppos, s.sent_id, s.`pos` spos";
        if (user_has_permission('perm_adder')) $q .= ", ss.status";
        $q .= "\nFROM paragraphs p
            LEFT JOIN sentences s
            ON (p.par_id = s.par_id)\n";

        if (user_has_permission('perm_adder')) $q .= "LEFT JOIN sentence_check ss ON (s.sent_id = ss.sent_id AND ss.status=1 AND ss.user_id=".$_SESSION['user_id'].")\n";
        $q .= "WHERE p.book_id = $book_id
            ORDER BY p.`pos`, s.`pos`";
        $res = sql_query($q);
        while ($r = sql_fetch_array($res)) {
            $res1 = sql_query("SELECT tf_id, tf_text FROM text_forms WHERE sent_id=".$r['sent_id']." ORDER BY pos");
            $tokens = array();
            while ($r1 = sql_fetch_array($res1)) {
                $tokens[] = array('text' => $r1['tf_text'], 'id' => $r1['tf_id']);
            }
            $out['paragraphs'][$r['ppos']][] = array('id' => $r['sent_id'], 'pos' => $r['spos'], 'tokens' => $tokens, 'checked' => $r['status']);
        }
    } else {
        $res = sql_query("SELECT p.`pos` ppos, s.sent_id, s.`pos` spos FROM paragraphs p LEFT JOIN sentences s ON (p.par_id = s.par_id) WHERE p.book_id = $book_id ORDER BY p.`pos`, s.`pos`");
        while ($r = sql_fetch_array($res)) {
            $r1 = sql_fetch_array(sql_query("SELECT source, SUBSTRING_INDEX(source, ' ', 6) AS `cnt` FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1"));
            if ($r1['source'] === $r1['cnt']) {
                $out['paragraphs'][$r['ppos']][] = array('pos' => $r['spos'], 'id' => $r['sent_id'], 'snippet' => $r1['source']);
                continue;
            }

            $snippet = '';

            $r1 = sql_fetch_array(sql_query("SELECT SUBSTRING_INDEX(source, ' ', 3) AS `start` FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1"));
            $snippet = $r1['start'];

            if ($snippet) $snippet .= '... ';

            $r1 = sql_fetch_array(sql_query("SELECT SUBSTRING_INDEX(source, ' ', -3) AS `end` FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1"));
            $snippet .= $r1['end'];

            $out['paragraphs'][$r['ppos']][] = array('pos' => $r['spos'], 'id' => $r['sent_id'], 'snippet' => $snippet);
        }
    }
    return $out;
}
function books_add($name, $parent_id=0, $go=0) {
    if ($name == '') {
        die ("Название не может быть пустым.");
    }
    if (sql_query("INSERT INTO `books` VALUES(NULL, '$name', '$parent_id')")) {
        if ($go) {
            header("Location:books.php?book_id=".sql_insert_id());
        } else {
            header("Location:books.php?book_id=$parent_id");
        }
        return;
    } else {
        show_error();
    }
}
function books_move($book_id, $to_id) {
    if ($book_id == $to_id) {
        header("Location:books.php?book_id=$book_id");
        return;
    }

    //to avoid loops
    $r = sql_fetch_array(sql_query("SELECT parent_id FROM books WHERE book_id=$to_id LIMIT 1"));
    if ($r['parent_id'] == $book_id) {
        header("Location:books.php?book_id=$book_id");
        return;
    }

    if (sql_query("UPDATE `books` SET `parent_id`='$to_id' WHERE `book_id`=$book_id LIMIT 1")) {
        header("Location:books.php?book_id=$to_id");
        return;
    } else {
        show_error();
    }
}
function books_rename($book_id, $name) {
    if ($name === '') {
        die ("Название не может быть пустым.");
    }
    if (sql_query("UPDATE `books` SET `book_name`='$name' WHERE `book_id`=$book_id LIMIT 1")) {
        header("Location:books.php?book_id=$book_id");
        return;
    } else {
        show_error();
    }
}
function get_books_for_select($parent = -1) {
    $out = array();
    $pg = $parent > -1 ? "WHERE `parent_id`=$parent " : '';
    $res = sql_query("SELECT `book_id`, `book_name` FROM `books` ".$pg."ORDER BY `book_name`", 0);
    while($r = sql_fetch_array($res)) {
        $out["$r[book_id]"] = $r['book_name'];
    }
    return $out;
}
function books_add_tag($book_id, $tag_name) {
    if ($book_id && $tag_name) {
        if (!sql_query("DELETE FROM `book_tags` WHERE book_id=$book_id AND tag_name='$tag_name'") || !sql_query("INSERT INTO `book_tags` VALUES('$book_id', '$tag_name')")) {
            die("Couldn't add tag");
        }
    }
    header("Location:books.php?book_id=$book_id");
    return;
}
function books_del_tag($book_id, $tag_name) {
    if ($book_id && $tag_name) {
        if (!sql_query("DELETE FROM `book_tags` WHERE book_id=$book_id AND tag_name='$tag_name'")) {
            die("Couldn't remove tag");
        }
    }
    header("Location:books.php?book_id=$book_id");
    return;
}
function merge_sentences($id1, $id2) {
    if ($id1 < 1 || $id2 < 1 || ($id2-$id1 != 1)) {
        show_error("Можно склеить только два соседних предложения!");
        return;
    }
    //moving tokens
    $r = sql_fetch_array(sql_query("SELECT MAX(pos) FROM text_forms WHERE sent_id=$id1"));
    if (!sql_query("UPDATE text_forms SET sent_id='$id1', pos=pos+".$r[0]." WHERE sent_id=$id2")) {
        show_error();
        return;
    }
    //merging source text
    $r1 = sql_fetch_array(sql_query("SELECT `source` FROM sentences WHERE sent_id=$id1 LIMIT 1"));
    $r2 = sql_fetch_array(sql_query("SELECT `source` FROM sentences WHERE sent_id=$id2 LIMIT 1"));
    if (!sql_query("UPDATE sentences SET `source`='".mysql_real_escape_string($r1['source'].' '.$r2['source'])."' WHERE sent_id=$id1 LIMIT 1")) {
        show_error();
        return;
    }
    //dropping status
    if (!sql_query("UPDATE sentences SET check_status='0' WHERE sent_id=$id1 LIMIT 1") ||
        !sql_query("DELETE FROM sentence_check WHERE sent_id=$id1") ||
        !sql_query("DELETE FROM sentence_check WHERE sent_id=$id2")) {
        show_error();
        return;
    }
    //deleting sentence
    if (sql_query("DELETE FROM sentences WHERE sent_id=$id2 LIMIT 1")) {
        header("Location:sentence.php?id=$id1");
        return;
    }
    show_error();
}
function merge_tokens($id_from, $id_to) {
    if ($id_from < 1 || $id_to < 1 || $id_from >= $id_to) {
        show_error("Неверные параметры.");
        return;
    }

    $r = sql_fetch_array(sql_query("SELECT tf_text FROM text_forms WHERE tf_id=$id_from LIMIT 1"));
    $new_text = $r['tf_text'];

    $res = sql_query("SELECT tf_id, tf_text FROM text_forms WHERE tf_id > $id_from AND tf_id <= $id_to ORDER BY pos");
    while($r = sql_fetch_array($res)) {
        //saving tf_text
        $new_text .= $r['tf_text'];
        if (
            //updating revisions
            !sql_query("UPDATE tf_revisions SET tf_id = '$id_from' WHERE tf_id = ".$r['tf_id']) ||
            //deleting from text_forms and form2tf
            !sql_query("DELETE FROM form2tf WHERE tf_id = ".$r['tf_id']) ||
            !sql_query("DELETE FROM text_forms WHERE tf_id = ".$r['tf_id'])
        ) {
            show_error();
            return;
        }
    }
    //updating text & adding a revision
    $revset_id = create_revset("Tokens $id_from to $id_to merged to <$new_text>");
    if (
        !sql_query("UPDATE text_forms SET tf_text = '".mysql_real_escape_string($new_text)."' WHERE tf_id=$id_from LIMIT 1") ||
        !sql_query("INSERT INTO `tf_revisions` VALUES(NULL, '$revset_id', '$id_from', '".mysql_real_escape_string(generate_tf_rev($new_text))."')")
    ) {
        show_error();
        return;
    }

    $r = sql_fetch_array(sql_query("SELECT sent_id FROM text_forms WHERE tf_id=$id_from LIMIT 1"));

    //dropping sentcnce status
    if (!sql_query("UPDATE sentences SET check_status='0' WHERE sent_id=".$r['sent_id']." LIMIT 1") ||
        !sql_query("DELETE FROM sentence_check WHERE sent_id=".$r['sent_id'])) {
        show_error();
        return;
    }
    header("Location:sentence.php?id=".$r['sent_id']);
}
function split_token($token_id, $num) {
    //$num is the number of characters (in the beginning) that should become a separate token
    if (!$token_id || !$num) {
        show_error("Неверные параметры.");
        return;
    }
    $res = sql_query("SELECT tf_text, sent_id, pos FROM text_forms WHERE tf_id=$token_id LIMIT 1");
    if (sql_num_rows($res) == 0) {
        show_error();
        return;
    }
    $r = sql_fetch_array($res);
    $text1 = trim(mb_substr($r['tf_text'], 0, $num));
    $text2 = trim(mb_substr($r['tf_text'], $num));
    if (!$text1 || !$text2) {
        show_error();
        return;
    }
    //create revset
    $revset_id = create_revset("Token $token_id (<".$r['tf_text'].">) split to <$text1> and <$text2>");
    if (
        //update other tokens in the sentence
        !sql_query("UPDATE text_forms SET pos=pos+1 WHERE sent_id = ".$r['sent_id']." AND pos > ".$r['pos']) ||
        //create new token and parse
        !sql_query("INSERT INTO text_forms VALUES(NULL, '".$r['sent_id']."', '".($r['pos'] + 1)."', '".mysql_real_escape_string($text2)."', '0')") ||
        !sql_query("INSERT INTO tf_revisions VALUES(NULL, '$revset_id', '".sql_insert_id()."', '".mysql_real_escape_string(generate_tf_rev($text2))."')") ||
        //update old token and parse
        !sql_query("UPDATE text_forms SET tf_text='".mysql_real_escape_string($text1)."', dict_updated='0' WHERE tf_id=$token_id LIMIT 1") ||
        !sql_query("INSERT INTO tf_revisions VALUES(NULL, '$revset_id', '$token_id', '".mysql_real_escape_string(generate_tf_rev($text1))."')")
    ) {
        show_error();
        return;
    }

    //dropping sentence status
    $r = sql_fetch_array(sql_query("SELECT sent_id FROM text_forms WHERE tf_id=$token_id LIMIT 1"));
    $sent_id = $r['sent_id'];

    if (!sql_query("UPDATE sentences SET check_status='0' WHERE sent_id=$sent_id LIMIT 1") ||
        !sql_query("DELETE FROM sentence_check WHERE sent_id=$sent_id")) {
        show_error();
        return;
    }
    
    $res = sql_query("SELECT book_id FROM paragraphs WHERE par_id = (SELECT par_id FROM sentences WHERE sent_id=$sent_id LIMIT 1)");
    $r = sql_fetch_array($res);

    header("Location:books.php?book_id=".$r['book_id']."&full#sen$sent_id");
}
?>
