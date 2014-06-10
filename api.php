<?php
require_once('lib/header_ajax.php');
require_once('lib/lib_annot.php');
require_once('lib/lib_books.php');
header('Content-type: application/json');

define('API_VERSION', '0.22');
$action = $_GET['action'];

$answer = array(
    'api_version' => API_VERSION,
    'answer' => null,
    'error' => null
);

function json_encode_readable($arr)
{
    //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
    array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
    return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
}

switch ($action) {
    case 'search':
        if (isset($_GET['all_forms']))
            $all_forms = (bool)$_GET['all_forms'];
        else
            $all_forms = false;

        $answer['answer'] = get_search_results($_GET['query'], !$all_forms);
        foreach ($answer['answer']['results'] as &$res) {
            $parts = array();
            foreach (get_book_parents($res['book_id'], true) as $p) {
                $parts[] = $p['title'];
            }
            $res['text_fullname'] = join(': ', array_reverse($parts));
        }
        break;
    default:
        $answer['error'] = 'Unknown action';
}

die(json_encode_readable($answer));
?>
