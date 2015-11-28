<?php

require_once('lib/common.php');
require_once("lib/lib_users.php");

function json($data) {
    header('Content-type: application/json');
    echo json_encode($data);
    die();
}

$config = parse_ini_file(__DIR__ . 'config.ini', true);
$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$pdo_db->query("SET NAMES utf8");

/*
 *      ACTIONS
 */

// return is success
// Exception is error
$actions = [
    'welcome' => function($data){
        return 'Welcome to opencorpora API v1.0!';
    },
    'search' => function($data){
        // if (isset($_GET['all_forms'])) {
        //     $all_forms = (bool)$_GET['all_forms'];
        // } else {
        //     $all_forms = false;
        // }
        // $answer['answer'] = get_search_results($_GET['query'], !$all_forms);
        // foreach ($answer['answer']['results'] as &$res) {
        //     $parts = array();
        //     foreach (get_book_parents($res['book_id'], true) as $p) {
        //         $parts[] = $p['title'];
        //     }
        //     $res['text_fullname'] = join(': ', array_reverse($parts));
        // }
    },
    'login_test' => function($data){
        if (isset($data->login) && isset($data->password)) {
            if($data->login == 'test' && $data->password == 'test') {
                return [
                    'token' => '1234qwer',
                    'user_id' => 1,
                ];
            }
        }
        throw new \Exception("invalid login:pass", 1);
    },

    'login' => function($data){
        var_dump(pdo());
        die();

        $user_id = user_check_password($data->login, $data->password);
        if ($user_id) {
            $token = remember_user($user_id, false, false);
            return ['result' => [
                'token' => $token,
                'user_id' => $user_id,
            ]];
        } else {
            return ['error' => 'Incorrect login or password'];
        }
    },
    'register' => function($data){
    },
    'get_available_morph_tasks' => function($data){
        // $answer['answer'] = array('tasks' => get_available_tasks($user_id, true));
    },
    'get_morph_task' => function($data){
        // if (empty($_POST['pool_id']) || empty($_POST['size']))
        //     throw new UnexpectedValueException("Wrong args");
        // // timeout is in seconds
        // $answer['answer'] = get_annotation_packet($_POST['pool_id'], $_POST['size'], $user_id, $_POST['timeout']);
    },
    'update_morph_task' => function($data){
        // throw new Exception("Not implemented");
    },
    'save_morph_task' => function($data){
        // answers is expected to be an array(array(id, answer), array(id, answer), ...)
        // update_annot_instances($user_id, $_POST['answers']);
    },
];




/*
 *     CHECKS
 */

$anonActions = ['search', 'login', 'welcome', 'login_test'];

if (!isset($_POST['action'])) {
    json(['error' => 'API required "action" field']);
}
// TODO: check_auth_token!!!
if (!in_array($_POST['action'], $anonActions)) {
    $token  = isset($_POST['token']) ? $_POST['token'] : false;
    if (!$token) {
        json(['error' => 'this API action require "token" field']);
    }
    $user_id = check_auth_token($_POST['token']);
    if (!$user_id) {
        json(['error' => 'Incorrect token']);
    }
}

// action REQUIRE, data OPTIONAL
$action = $_POST['action'];
$data   = isset($_POST['data']) ? json_decode($_POST['data']) : false;

if (isset($actions[$action])) {
    try {
        $answer = ['result' => $actions[$action]($data)];
    } catch (\Exception $e) {
        $answer = ['error' => $e->getMessage()];
    }
} else {
    $answer = ['error' => 'Unknown action'];
}
json($answer);
