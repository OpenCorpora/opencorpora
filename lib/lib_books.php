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
        'select' => get_books_for_select(),
        'is_wikinews' => (int)$r['parent_id'] == 56
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
                if (preg_match('/^http:\/\/ru.wikinews.org\/wiki\/(.+)$/', $matches[2], $wn_matches)) {
                    $out['wikinews_title'] = str_replace('_', ' ', $wn_matches[1]);
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
function books_add($name, $parent_id=0) {
    if ($name === '') {
        die ("Название не может быть пустым.");
    }
    if (sql_query("INSERT INTO `books` VALUES(NULL, '$name', '$parent_id')")) {
        return 1;
    }
    return 0;
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
    $tag_name = trim($tag_name);
    if ($book_id && $tag_name) {
        if (!sql_query("DELETE FROM `book_tags` WHERE book_id=$book_id AND tag_name='$tag_name'") || !sql_query("INSERT INTO `book_tags` VALUES('$book_id', '$tag_name')")) {
            return 0;
        }
    }
    return 1;
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
function download_url($url) {
    if (!$url) return 0;
    
    //check if it has been already downloaded
    $res = sql_query("SELECT url FROM downloaded_urls WHERE url='".mysql_real_escape_string($url)."' LIMIT 1");
    if (sql_num_rows($res) > 0) {
        return 0;
    }

    //downloading
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'OpenCorpora.org bot');
    $contents = curl_exec($ch);
    curl_close($ch);

    //writing to disk
    $filename = uniqid('', 1);
    $res = file_put_contents("/corpus/files/saved/$filename.html", $contents);
    if (!$res) {
        return 0;
    }

    if (sql_query("INSERT INTO downloaded_urls VALUES('".mysql_real_escape_string($url)."', '$filename')")) {
        return $filename;
    }
    return 0;
}
function split_paragraph($sentence_id) {
    if (!$sentence_id) {
        show_error();
        return;
    }
    //get pos
    $r = sql_fetch_array(sql_query("SELECT pos FROM sentences WHERE sent_id=$sentence_id LIMIT 1"));
    $spos = $r['pos'];
    //get the parahraph info
    $r = sql_fetch_array(sql_query("SELECT par_id, book_id, pos FROM paragraphs WHERE par_id=(SELECT par_id FROM sentences WHERE sent_id=$sentence_id LIMIT 1) LIMIT 1"));
    //move the following paragraphs
    sql_query("UPDATE paragraphs SET pos=pos+1 WHERE book_id=".$r['book_id']." AND pos > ".$r['pos']);
    //make a new paragraph
    sql_query("INSERT INTO paragraphs VALUES(NULL, '".$r['book_id']."', '".($r['pos']+1)."')");
    $new_par_id = sql_insert_id();
    //move the following sentences to the new paragraph
    if (sql_query("UPDATE sentences SET par_id=$new_par_id, pos=pos-$spos WHERE par_id=".$r['par_id']." AND pos > $spos")) {
        header("Location:books.php?book_id=".$r['book_id']."&full#sen$sentence_id");
        return;
    }
    show_error();
}
function split_sentence($token_id) {
    //note: comments will stay with the first sentence

    //find which sentence the token is in
    $r = sql_fetch_array(sql_query("SELECT sent_id, pos FROM text_forms WHERE tf_id=$token_id LIMIT 1"));
    $sent_id = $r['sent_id'];
    $tpos = $r['pos'];
    //check that it is not the last token
    $r = sql_fetch_array(sql_query("SELECT MAX(pos) mpos FROM text_forms WHERE sent_id=$sent_id"));
    if ($r['mpos'] == $tpos) return 0;
    //split the source field
    $r = sql_fetch_array(sql_query("SELECT source, pos, par_id FROM sentences WHERE sent_id=$sent_id LIMIT 1"));
    $source = $r['source'];
    $spos = $r['pos'];
    $par_id = $r['par_id'];
    $res = sql_query("SELECT tf_text FROM text_forms WHERE sent_id=$sent_id AND pos<=$tpos ORDER BY pos");
    $t = 0;
    while ($r = sql_fetch_array($res)) {
       while (mb_substr($source, $t, mb_strlen($r['tf_text'], 'UTF-8'), 'UTF-8') !== $r['tf_text']) {
           $t++;
           if ($t > mb_strlen($source, 'UTF-8'))
               return 0;
       }
       $t += mb_strlen($r['tf_text'], 'UTF-8');
    }
    $source_left = trim(mb_substr($source, 0, $t, 'UTF-8'));
    $source_right = trim(mb_substr($source, $t, mb_strlen($source, 'UTF-8')-1, 'UTF-8'));
    //shift the following sentences
    if (
        !sql_query("UPDATE sentences SET pos=pos+1 WHERE par_id=$par_id AND pos > $spos") ||
    //create new sentence
        !sql_query("INSERT INTO sentences VALUES(NULL, '$par_id', '".($spos+1)."', '".mysql_real_escape_string($source_right)."', '0')")
    ) return 0;
    $new_sent_id = sql_insert_id();
    //move tokens
    if (
        !sql_query("UPDATE text_forms SET sent_id=$new_sent_id, pos=pos-$tpos WHERE sent_id=$sent_id AND pos>$tpos") ||
    //change source in the original sentence
        !sql_query("UPDATE sentences SET check_status='0', source='".mysql_real_escape_string($source_left)."' WHERE sent_id=$sent_id LIMIT 1") ||
    //drop status
        !sql_query("DELETE FROM sentence_check WHERE sent_id=$sent_id")
    ) return 0;
    $r = sql_fetch_array(sql_query("SELECT book_id FROM paragraphs WHERE par_id=$par_id LIMIT 1"));
    return array($r['book_id'], $sent_id);
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
function merge_tokens_ii($id_array) {
    //ii stands for "id insensitive"
    if (sizeof($id_array) < 2) {
        return 0;
    }
    $id_array = array_map(intval, $id_array);
    $joined = join(',', $id_array);

    //check if they are all in the same sentence
    $res = sql_query("SELECT distinct sent_id FROM text_forms WHERE tf_id IN($joined)");
    if (sql_num_rows($res) > 1) {
        return 0;
    }
    $r = sql_fetch_array($res);
    $sent_id = $r['sent_id'];
    //check if they all stand in a row
    $r = sql_fetch_array(sql_query("SELECT MIN(pos) AS minpos, MAX(pos) AS maxpos FROM text_forms WHERE tf_id IN($joined)"));
    $res = sql_query("SELECT tf_id FROM text_forms WHERE sent_id=$sent_id AND pos > ".$r['minpos']." AND pos < ".$r['maxpos']." AND tf_id NOT IN ($joined) LIMIT 1");
    if (sql_num_rows($res) > 0) {
        return 0;
    }
    //assemble new token, delete others from form2tf and text_forms, update tf_id in their revisions
    $res = sql_query("SELECT tf_id, tf_text FROM text_forms WHERE tf_id IN ($joined) ORDER BY pos");
    $r = sql_fetch_array($res);
    $new_id = $r['tf_id'];
    $new_text = $r['tf_text'];
    while ($r = sql_fetch_array($res)) {
        $new_text .= $r['tf_text'];
        if (!sql_query("UPDATE tf_revisions SET tf_id=$new_id WHERE tf_id=".$r['tf_id']) ||
            !sql_query("DELETE FROM form2tf WHERE tf_id=".$r['tf_id']) ||
            !sql_query("DELETE FROM text_forms WHERE tf_id=".$r['tf_id'])) {
            return 0;
        }
    }
    //update tf_text, add new revision
    $revset_id = create_revset("Tokens $joined merged to <$new_text>");
    if (
        !sql_query("UPDATE text_forms SET tf_text = '".mysql_real_escape_string($new_text)."' WHERE tf_id=$new_id LIMIT 1") ||
        !sql_query("INSERT INTO `tf_revisions` VALUES(NULL, '$revset_id', '$new_id', '".mysql_real_escape_string(generate_tf_rev($new_text))."')")
    ) {
        return 0;
    }
    //drop sentence status
    if (!sql_query("UPDATE sentences SET check_status='0' WHERE sent_id=$sent_id LIMIT 1") ||
        !sql_query("DELETE FROM sentence_check WHERE sent_id=$sent_id")) {
        return 0;
    }
    
    return 1;
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

// book adding queue

function get_sources_page($skip = 0, $show_type = '') {
    $out = array();
    $q_main = "SELECT s.source_id, s.url, s.title, s.user_id, s.book_id, u.user_name, b.book_name FROM sources s LEFT JOIN books b ON (s.book_id = b.book_id) LEFT JOIN users u ON (s.user_id = u.user_id) ";
    $q_cnt = "SELECT COUNT(*) AS cnt FROM sources s ";
    if ($show_type == 'my')
        $q_tail = "WHERE s.user_id = ".$_SESSION['user_id'];
    elseif ($show_type == 'active')
        $q_tail = "WHERE s.user_id > 0 OR s.book_id > 0";
    elseif ($show_type == 'free')
        $q_tail = "WHERE s.user_id = 0";
    $q_tail2 = $show_type == 'free' ? " ORDER BY RAND() LIMIT 200" : " ORDER BY s.book_id DESC, s.source_id LIMIT $skip,200";
    $r = sql_fetch_array(sql_query($q_cnt.$q_tail));
    $out['total'] = $r['cnt'];
    $res = sql_query($q_main.$q_tail.$q_tail2);
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT `user_id`, `status`, `timestamp` FROM sources_status WHERE source_id=".$r['source_id']." ORDER BY `timestamp` DESC LIMIT 1"));
        $comments = array();
        $res1 = sql_query("SELECT user_name, text, timestamp FROM sources_comments sc LEFT JOIN users u ON (sc.user_id=u.user_id) WHERE sc.source_id=".$r['source_id']." ORDER BY comment_id");
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
    if (!$url) {
        show_error();
        return;
    }
    
    if (sql_query("INSERT INTO sources VALUES(NULL, '$parent_id', '".mysql_real_escape_string($url)."', '".mysql_real_escape_string($title)."', '0', '0')")) {
        header("Location:sources.php");
    }
}

?>
