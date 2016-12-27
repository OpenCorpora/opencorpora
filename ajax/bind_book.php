<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_books.php');

try {
    check_permission(PERM_ADDER);
    $sid = POST('sid');
    $book_id = POST('book_id');

    sql_begin();
    //creating book if necessary
    if ($book_id == -1) {
        //find the parent id
        $res = sql_pe("SELECT book_id, url FROM sources WHERE source_id = (SELECT parent_id FROM sources WHERE source_id=? LIMIT 1) LIMIT 1", array($sid));
        if (!$res[0]['book_id'])
            throw new UnexpectedValueException();

        $book_id = books_add(POST('book_name'), $res[0]['book_id']);

        $res = sql_pe("SELECT url FROM sources WHERE source_id=? LIMIT 1", array($sid));
        books_add_tag($book_id, 'url:'.$res[0]['url']);
        download_url($res[0]['url']);
    }

    //bind
    sql_pe("UPDATE sources SET book_id=? WHERE source_id=? LIMIT 1", array($book_id, $sid));
    sql_commit();
    $res = sql_pe("SELECT book_name FROM books WHERE book_id=? LIMIT 1", array($book_id));
    $result['title'] = htmlspecialchars($res[0]['book_name']);
    $result['book_id'] = $book_id;
}
catch (Exception $e) {
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
