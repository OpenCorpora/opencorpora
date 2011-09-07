<?php
require_once('../lib/header.php');
require_once('../lib/lib_books.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

if (!user_has_permission('perm_adder')) {
    return;
}

if (!isset($_GET['sid']) || !isset($_GET['book_id'])) {
    echo '<result ok="0"/>';
    return;
}

$sid = (int)$_GET['sid'];
$book_id = (int)$_GET['book_id'];

sql_begin();
//creating book if necessary
if ($book_id == -1) {
    //find the parent id
    $res = sql_query("SELECT book_id, url FROM sources WHERE source_id = (SELECT parent_id FROM sources WHERE source_id=$sid LIMIT 1) LIMIT 1");
    $r = sql_fetch_array($res);
    if (!isset($_GET['book_name']) ||
        !$r['book_id'] ||
        !books_add(mysql_real_escape_string($_GET['book_name']), $r['book_id'])
    ) {
        echo '<result ok="0"/>';
        return;
    }
    $book_id = sql_insert_id();
    $r = sql_fetch_array(sql_query("SELECT url FROM sources WHERE source_id=$sid LIMIT 1"));
    if (!books_add_tag($book_id, 'url:'.$r['url']) || !download_url($r['url'])) {
        echo '<result ok="0"/>';
        return;
    }
    $r = sql_fetch_array(sql_query("SELECT book_name FROM books WHERE book_id=$book_id LIMIT 1"));
}

//bind
if (sql_query("UPDATE sources SET book_id='$book_id' WHERE source_id=$sid LIMIT 1")) {
    sql_commit();
    $r = sql_fetch_array(sql_query("SELECT book_name FROM books WHERE book_id=$book_id LIMIT 1"));
    echo '<result ok="1" title="'.htmlspecialchars($r['book_name']).'" book_id="'.$book_id.'"/>';
} else {
    echo '<result ok="0"/>';
}
?>
