<?php
require_once('lib/header_ajax.php');
require_once('lib/lib_annot.php');
require_once('lib/lib_books.php');
require_once('lib/lib_users.php');
require_once('lib/lib_morph_pools.php');
header('Content-type: application/json');

define('API_VERSION', '0.31');
$action = $_GET['action'];
$user_id = 0;

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


// check token for most action types
if (!in_array($action, array('search', 'login'))) {
    $user_id = check_auth_token($_POST['user_id'], $_POST['token']);
    if (!$user_id)
        throw new Exception('Incorrect token');
}

try {
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
    case 'login':
        $user_id = user_check_password($_POST['login'], $_POST['password']);
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
        if (empty($_POST['pool_id']) || empty($_POST['size']))
            throw new UnexpectedValueException("Wrong args");
        // timeout is in seconds
        $answer['answer'] = get_annotation_packet($_POST['pool_id'], $_POST['size'], $user_id, $_POST['timeout']);
        break;
    case 'update_morph_task':
        throw new Exception("Not implemented");
        // currently no backend
        break;
    case 'save_morph_task':
        // answers is expected to be an array(array(id, answer), array(id, answer), ...)
        update_annot_instances($user_id, $_POST['answers']);
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
