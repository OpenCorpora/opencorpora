<?php
function get_page_tok_strange($newest = false) {
    $res = sql_query("SELECT timestamp, param_value FROM stats_values WHERE param_id=7 ORDER BY timestamp DESC LIMIT 1");
    $r = sql_fetch_array($res);
    $out = array('timestamp' => $r['timestamp'], 'coeff' => $r['param_value']/1000, 'broken' => array(), 'items' => array());
    $res = sql_query("SELECT param_value FROM stats_values WHERE param_id=28 ORDER BY param_value");
    while ($r = sql_fetch_array($res)) {
        $tid = $r['param_value'];
        $r1 = sql_fetch_array(sql_query("SELECT tf_text, sent_id FROM text_forms WHERE tf_id=$tid LIMIT 1"));
        $out['broken'][] = array(
            'token_text' => $r1['tf_text'],
            'sent_id' => $r1['sent_id']
        );
    }
    $comments = array();
    $res = sql_query("SELECT ts.sent_id, ts.pos, ts.border, ts.coeff, s.source, p.book_id FROM tokenizer_strange ts LEFT JOIN sentences s ON (ts.sent_id=s.sent_id) LEFT JOIN paragraphs p ON (s.par_id=p.par_id) ORDER BY ".($newest ? "ts.sent_id DESC" : "ts.coeff DESC"));
    while ($r = sql_fetch_array($res)) {
        if (!isset($comments[$r['sent_id']])) {
            $res1 = sql_query("SELECT comment_id FROM sentence_comments WHERE sent_id=".$r['sent_id']." LIMIT 1");
            $comments[$r['sent_id']] = sql_num_rows($res1) > 0 ? 1 : -1;
        }
        $out['items'][] = array(
            'sent_id' => $r['sent_id'],
            'book_id' => $r['book_id'],
            'coeff' => $r['coeff'], 
            'border' => $r['border'], 
            'lcontext' => mb_substr($r['source'], max(0, $r['pos']-10), min(10, $r['pos'])),
            'focus' => mb_substr($r['source'], $r['pos'], 1),
            'rcontext' => mb_substr($r['source'], $r['pos']+1, 9),
            'comments' => $comments[$r['sent_id']]
        );
    }
    return $out;
}
function get_page_sent_strange() {
    $out = array();
    $res = sql_query("SELECT sent_id FROM sentences_strange ORDER BY sent_id DESC");
    while ($r = sql_fetch_array($res)) {
        $r1 = sql_fetch_array(sql_query("SELECT source FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1"));
        $r2 = sql_fetch_array(sql_query("SELECT book_id FROM paragraphs WHERE par_id = (SELECT par_id FROM sentences WHERE sent_id=".$r['sent_id']." LIMIT 1) LIMIT 1"));
        $out[] = array('id' => $r['sent_id'], 'text' => $r1['source'], 'book_id' => $r2['book_id']);
    }
    return $out;
}
function get_empty_books() {
    $res = sql_query("
        SELECT book_id, book_name
        FROM books
        WHERE book_id NOT IN (SELECT DISTINCT book_id FROM paragraphs)
        AND book_id NOT IN (SELECT DISTINCT parent_id FROM books)
    ");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[] = array('id' => $r['book_id'], 'name' => $r['book_name']);
    }
    return $out;
}
function get_downloaded_urls() {
    $res = sql_query("
        SELECT b.book_id, b.book_name, SUBSTR(t.tag_name, 5) url, u.filename
        FROM book_tags t
        LEFT JOIN books b
        ON (t.book_id = b.book_id)
        LEFT JOIN downloaded_urls u
        ON (SUBSTR(t.tag_name, 5) = u.url)
        WHERE t.tag_name LIKE 'url:%'
        ORDER BY b.book_id DESC
    ");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[] = array(
            'book_id' => $r['book_id'],
            'book_name' => $r['book_name'],
            'url' => $r['url'],
            'filename' => $r['filename'],
            'exists' => file_exists('files/saved/'.$r['filename'].'.html') ? 1 : 0
        );
    }
    return $out;
}
function get_tag_errors() {
    $res = sql_query("SELECT * FROM tag_errors ORDER BY book_id DESC");
    $out = array();
    while ($r = sql_fetch_array($res)) {
        $out[] = array(
            'book_id' => $r['book_id'],
            'tag_name' => $r['tag_name'],
            'error_type' => $r['error_type']
        );
    }
    return $out;
}
function get_good_sentences($no_zero = false) {
    $where = $no_zero ? "WHERE num_homonymous > 0" : "";
    $out = array();
    $res = sql_query("SELECT sent_id, num_words, num_homonymous FROM good_sentences $where ORDER BY (num_homonymous / num_words), num_words desc LIMIT 1000");
    while ($r = sql_fetch_array($res))
        $out[] = array('id' => $r['sent_id'], 'total' => $r['num_words'], 'homonymous' => $r['num_homonymous']);
    return $out;
}
function get_merge_fails() {
    $res = sql_query("
        SELECT sample_id, p.pool_name, ms.status
        FROM morph_annot_moderated_samples ms
        LEFT JOIN morph_annot_samples USING (sample_id)
        LEFT JOIN morph_annot_pools p USING (pool_id)
        WHERE p.status = 9
        AND merge_status = 0
        ORDER BY sample_id
    ");

    $data = array();
    while ($r = sql_fetch_array($res)) {
        $data[] = array(
            'id' => $r['sample_id'],
            'mod_status' => $r['status'],
            'pool_name' => $r['pool_name']
        );
    }
    return $data;
}
?>
