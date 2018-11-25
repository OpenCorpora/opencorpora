<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

set_include_path(get_include_path().PATH_SEPARATOR.'/corpus');
require_once('lib/header_ajax.php');

define('VERSION', "0.12");
$TAGS = sql_prepare("SELECT tag_name FROM book_tags WHERE book_id=?");
$PARAGRAPHS = sql_prepare("SELECT par_id FROM paragraphs WHERE book_id=? ORDER BY pos");
$SENTENCES = sql_prepare("SELECT sent_id, source FROM sentences WHERE par_id=? ORDER BY pos");
$TOKENS = sql_prepare("
    SELECT tf_id, tf_text, rev_id, rev_text
    FROM tokens
    JOIN tf_revisions
        USING (tf_id)
    WHERE sent_id=? AND is_last=1
    ORDER BY pos
");

main();


function main() {
    $xw = new XMLWriter();
    $xw->openUri('php://output');
    $xw->startDocument("1.0", "utf-8", "yes");
    $xw->startElement("annotation");
    $maxrev = sql_fetchall(sql_query("SELECT MAX(rev_id) AS m FROM tf_revisions"))[0]['m'];
    $xw->writeAttribute("version", VERSION);
    $xw->writeAttribute("revision", $maxrev);
    $xw->setIndent(true);
    $xw->setIndentString("  ");
    $books = sql_fetchall(sql_query("SELECT * FROM books"));
    foreach ($books as $book) {
        add_book($xw, $book);
    }
    $xw->endElement();
    $xw->endDocument();
}

function add_book($xw, $book) {
    $xw->startElement("text");
    $xw->writeAttribute("id", $book['book_id']);
    $xw->writeAttribute("parent", $book['parent_id']);
    $xw->writeAttribute("name", $book['book_name']);

    global $TAGS;
    $xw->startElement("tags");
    sql_execute($TAGS, array($book['book_id']));
    foreach (sql_fetchall($TAGS) as $tag) {
        $xw->writeElement("tag", $tag['tag_name']);
    }
    $xw->endElement();

    global $PARAGRAPHS;
    $xw->startElement("paragraphs");
    sql_execute($PARAGRAPHS, array($book['book_id']));
    foreach (sql_fetchall($PARAGRAPHS) as $paragraph) {
        add_paragraph($xw, $paragraph);
    }
    $xw->endElement();

    $xw->endElement();
}

function add_paragraph($xw, $paragraph) {
    global $SENTENCES;
    $xw->startElement("paragraph");
    $xw->writeAttribute("id", $paragraph['par_id']);
    sql_execute($SENTENCES, array($paragraph['par_id']));
    foreach (sql_fetchall($SENTENCES) as $sentence) {
        add_sentence($xw, $sentence);
    }
    $xw->endElement();
}

function add_sentence($xw, $sentence) {
    global $TOKENS;
    $xw->startElement("sentence");
    $xw->writeAttribute("id", $sentence['sent_id']);
    $xw->writeElement("source", $sentence['source']);
    $xw->startElement("tokens");
    sql_execute($TOKENS, array($sentence['sent_id']));
    foreach (sql_fetchall($TOKENS) as $token) {
        add_token($xw, $token);
    }
    $xw->endElement();
    $xw->endElement();
}

function add_token($xw, $token) {
    $xw->startElement("token");
    $xw->writeAttribute("id", $token['tf_id']);
    $xw->writeAttribute("text", $token['tf_text']);
    $xw->writeRaw(str_replace("<tfr", "<tfr rev_id=\"".$token['rev_id']."\"", $token['rev_text']));
    $xw->endElement();
}


?>
