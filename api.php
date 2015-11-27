<?php

require_once('lib/header_ajax.php');
require_once('lib/lib_annot.php');
require_once('lib/lib_books.php');
require_once('lib/lib_users.php');
require_once('lib/lib_morph_pools.php');

$user_id = 0;

$action = $_POST['action'];
$data   = isset($_POST['data']) ? json_decode($_POST['data']) : false;

// TODO: token in header. only token for auth
if (!in_array($action, array('search', 'login'))) {
    $user_id = check_auth_token($_POST['user_id'], $_POST['token']);
    if (!$user_id) {
        log_timing();
        echo json_encode(['error' => 'Incorrect token']);
        die();
    }
}

// registered actions
$actions = [
    'search' = function($data){
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
    },
    'login' = function($data){
        $user_id = user_check_password($_POST['login'], $_POST['password']);
        if ($user_id) {
            $token = remember_user($user_id, false, false);
            $answer['answer'] = array('user_id' => $user_id, 'token' => $token);
        }
        else
            $answer['error'] = 'Incorrect login or password';
    },
    'get_available_morph_tasks' = function($data){
        $answer['answer'] = array('tasks' => get_available_tasks($user_id, true));
    },
    'get_morph_task' = function($data){
        if (empty($_POST['pool_id']) || empty($_POST['size']))
            throw new UnexpectedValueException("Wrong args");
        // timeout is in seconds
        $answer['answer'] = get_annotation_packet($_POST['pool_id'], $_POST['size'], $user_id, $_POST['timeout']);
    },
    'update_morph_task' = function($data){
        throw new Exception("Not implemented");
    },
    'save_morph_task' = function($data){
        // answers is expected to be an array(array(id, answer), array(id, answer), ...)
        update_annot_instances($user_id, $_POST['answers']);
    },
];

if (isset($actions[$action])) {
    try {
        $result = ['success' => $actions[$action]($data)];
    } catch (\Exception $e) {
        $result = ['error' => $e->getMessage()];
    }
} else {
    $result = ['error' => 'Unknown action'];
}
log_timing();
echo json_encode($result);
