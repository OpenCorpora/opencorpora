<?php
require_once('constants.php');

function get_page_tok_strange($newest = false) {
    $res = sql_query("SELECT timestamp, param_value FROM stats_values WHERE param_id=7 ORDER BY timestamp DESC LIMIT 1");
    $r = sql_fetch_array($res);
    $out = array(
        'timestamp' => $r['timestamp'],
        'coeff' => $r['param_value'] / 1000,
        'broken' => array(),
        'items' => array()
    );
    $res = sql_fetchall(sql_query("SELECT param_value FROM stats_values WHERE param_id=28 ORDER BY param_value"));
    $res1 = sql_prepare("SELECT tf_text, sent_id FROM tokens WHERE tf_id=? LIMIT 1");
    foreach ($res as $r) {
        sql_execute($res1, array($r['param_value']));
        $r1 = sql_fetch_array($res1);
        $out['broken'][] = array(
            'token_text' => $r1['tf_text'],
            'sent_id' => $r1['sent_id']
        );
    }
    $res1->closeCursor();
    $comments = array();
    $res = sql_query("SELECT ts.sent_id, ts.pos, ts.border, ts.coeff, s.source, p.book_id FROM tokenizer_strange ts LEFT JOIN sentences s ON (ts.sent_id=s.sent_id) LEFT JOIN paragraphs p ON (s.par_id=p.par_id) ORDER BY ".($newest ? "ts.sent_id DESC" : "ts.coeff DESC"));
    $res1 = sql_prepare("SELECT comment_id FROM sentence_comments WHERE sent_id=? LIMIT 1");
    foreach (sql_fetchall($res) as $r) {
        if (!isset($comments[$r['sent_id']])) {
            sql_execute($res1, array($r['sent_id']));
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
    $res = sql_query("
        SELECT DISTINCT sent_id, source, book_id
        FROM sentences_strange
        LEFT JOIN sentences USING (sent_id)
        LEFT JOIN paragraphs USING (par_id)
        ORDER BY sent_id DESC
    ");
    while ($r = sql_fetch_array($res))
        $out[] = array('id' => $r['sent_id'], 'text' => $r['source'], 'book_id' => $r['book_id']);
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
        SELECT sample_id, p.pool_name, p.revision AS pool_revision, ms.status, s.tf_id
        FROM morph_annot_moderated_samples ms
        LEFT JOIN morph_annot_samples s USING (sample_id)
        LEFT JOIN morph_annot_pools p USING (pool_id)
        WHERE p.status = ".MA_POOLS_STATUS_ARCHIVED."
        AND merge_status = 0
        ORDER BY sample_id
    ");
            
    $res1 = sql_prepare("
        SELECT rev_id
        FROM tf_revisions tfr
        LEFT JOIN rev_sets USING (set_id)
        WHERE tf_id = ?
        AND rev_id > ?
        ORDER BY rev_id
        LIMIT 1
    ");

    $data = array(
        'samples' => array(),
        'total' => array()
    );

    while ($r = sql_fetch_array($res)) {
        sql_execute($res1, array($r['tf_id'], $r['pool_revision']));
        $r1 = sql_fetch_array($res1);

        if (!in_array($r['status'], array(3, 4))) {
            if ($r1)
                $r['status'] = 5;
            else
                $r['status'] = -1;
        }

        $data['samples'][] = array(
            'id' => $r['sample_id'],
            'mod_status' => $r['status'],
            'pool_name' => $r['pool_name'],
            'revision' => $r1['rev_id']
        );
        if (!isset($data['total'][$r['status']]))
            $data['total'][$r['status']] = 0;
        ++$data['total'][$r['status']];
    }
    return $data;
}
function get_most_useful_pools($type=0) {
    $res = sql_pe("
        SELECT pool_id, pool_name, p.status, user_name,
            COUNT(sent_id) cnt
        FROM morph_annot_samples
            LEFT JOIN morph_annot_pools p
                USING (pool_id)
            LEFT JOIN users
                ON (p.moderator_id = users.user_id)
            LEFT JOIN tokens tf
                USING (tf_id)
            RIGHT JOIN good_sentences
                USING (sent_id)
            LEFT JOIN tokens tf2
                USING (sent_id)
        WHERE p.status >= ".MA_POOLS_STATUS_ANSWERED."
            AND p.status <= ".MA_POOLS_STATUS_MODERATED."
            AND num_homonymous = 1
            AND (p.pool_type = ?
            ".($type == 0 ? "OR TRUE)" : ")")."
        GROUP BY pool_id
        ORDER BY COUNT(sent_id) DESC
        LIMIT 50
    ", array($type));
    $out = array();
    foreach ($res as $r)
        $out[] = array(
            'id' => $r['pool_id'],
            'name' => $r['pool_name'],
            'status' => $r['status'],
            'moderator' => $r['user_name'],
            'count' => $r['cnt']
        );
    return $out;
}
?>
