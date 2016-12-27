<?php
require_once('lib/header_ajax.php');
require_once('lib/lib_annot.php');
require_once('lib/lib_books.php');
require_once('lib/lib_users.php');
require_once('lib/lib_morph_pools.php');
header('Content-type: application/json');

define('API_VERSION', '0.32');
$action = GET('action', '');
$user_id = 0;

$answer = array(
    'api_version' => API_VERSION,
    'answer' => null,
    'error' => null
);

function json_encode_readable($arr)
{
    //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
    array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff)); });
    return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff));
}


// check token for most action types
if (!in_array($action, array('search', 'login'))) {
    $user_id = check_auth_token(POST('user_id'), POST('token'));
    if (!$user_id)
        throw new Exception('Incorrect token');
}

try {
switch ($action) {
    case 'search':
        $all_forms = (bool)GET('all_forms', false);

        $answer['answer'] = get_search_results(GET('query'), !$all_forms);
        foreach ($answer['answer']['results'] as &$res) {
            $parts = array();
            foreach (get_book_parents($res['book_id'], true) as $p) {
                $parts[] = $p['title'];
            }
            $res['text_fullname'] = join(': ', array_reverse($parts));
        }
        break;
    case 'login':
        $user_id = user_check_password(POST('login'), POST('password'));
        if ($user_id) {
            $token = remember_user($user_id, false, false);
            $answer['answer'] = array('user_id' => $user_id, 'token' => $token);
        }
        else
            $answer['error'] = 'Incorrect login or password';
        break;
    case 'get_available_morph_tasks':
        $answer['answer'] = array('tasks' => get_available_tasks($user_id, true));
        break;
    case 'get_morph_task':
        // timeout is in seconds
        $answer['answer'] = get_annotation_packet(POST('pool_id'), POST('size'), $user_id, POST('timeout'));
        break;
    case 'update_morph_task':
        throw new Exception("Not implemented");
        // currently no backend
        break;
    case 'save_morph_task':
        // answers is expected to be an array(array(id, answer), array(id, answer), ...)
        update_annot_instances($user_id, POST('answers'));
        break;
    default:
        throw new Exception('Unknown action');
}
} catch (Exception $e) {
    $answer['error'] = $e->getMessage();
}

log_timing();
die(json_encode_readable($answer));
?>
