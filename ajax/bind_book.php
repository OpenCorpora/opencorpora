<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if (!user_has_permission('perm_adder')) {
    return;
}

try {
    if (!isset($_GET['sid']) || !isset($_GET['book_id']))
        throw new UnexpectedValueException();
    $sid = (int)$_GET['sid'];
    $book_id = (int)$_GET['book_id'];

    sql_begin(true);
    //creating book if necessary
    if ($book_id == -1) {
        //find the parent id
        $res = sql_query_pdo("SELECT book_id, url FROM sources WHERE source_id = (SELECT parent_id FROM sources WHERE source_id=$sid LIMIT 1) LIMIT 1");
        $r = sql_fetch_array($res);
        if (!isset($_GET['book_name']) || !$r['book_id'])
            throw new UnexpectedValueException();

        $book_id = books_add(mysql_real_escape_string($_GET['book_name']), $r['book_id']);

        $r = sql_fetch_array(sql_query_pdo("SELECT url FROM sources WHERE source_id=$sid LIMIT 1"));
        books_add_tag($book_id, 'url:'.$r['url']);
        download_url($r['url']);
    }

    //bind
    sql_query_pdo("UPDATE sources SET book_id='$book_id' WHERE source_id=$sid LIMIT 1");
    sql_commit(true);
    $r = sql_fetch_array(sql_query_pdo("SELECT book_name FROM books WHERE book_id=$book_id LIMIT 1"));
    echo '<result ok="1" title="'.htmlspecialchars($r['book_name']).'" book_id="'.$book_id.'"/>';
}
catch (Exception $e) {
    echo '<result ok="0"/>';
}
?>
