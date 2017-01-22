<?php
require('lib/header.php');
require('lib/lib_qa.php');
require('lib/lib_morph_pools.php');

$action = GET('act', '');

switch ($action) {
    case 'sent_split':
        $smarty->assign('sentences', get_page_sent_strange());
        $smarty->display('qa/sent_split.tpl');
        break;
    case 'tokenizer':
        $smarty->assign('obj', get_page_tok_strange(GET('newest', 0)));
        $smarty->display('qa/tokenizer.tpl');
        break;
    case 'empty_books':
        $smarty->assign('books', get_empty_books());
        $smarty->display('qa/empty_books.tpl');
        break;
    case 'dl_urls':
        $smarty->assign('urls', get_downloaded_urls());
        $smarty->display('qa/dl_urls.tpl');
        break;
    case 'book_tags':
        $smarty->assign('errata', get_tag_errors());
        $smarty->display('qa/book_tags.tpl');
        break;
    case 'merge_fails':
        $smarty->assign('data', get_merge_fails(GET('status', 0)));
        $smarty->display('qa/merge_fails.tpl');
        break;
    case 'good_sentences':
        $smarty->assign('sentences', get_good_sentences(GET('no_zero', 0)));
        $smarty->display('qa/good_sentences.tpl');
        break;
    case 'useful_pools':
        $smarty->assign('pools', get_most_useful_pools(GET('type', 0)));
        $smarty->assign('types', get_morph_pool_types());
        $smarty->display('qa/useful_pools.tpl');
        break;
    case 'unkn':
        $smarty->assign('tokens', get_unknowns());
        $smarty->display('qa/unknowns.tpl');
        break;
    default:
        header("Location:index.php");
}
log_timing();

?>
