<?php

require_once('lib/common.php');
require_once("lib/lib_users.php");
require_once("lib/lib_annot.php");

function json($data) {
    header('Content-type: application/json');
    echo json_encode($data);
    die();
}

function requireFields($data, $fields)
{
    foreach ($fields as $field) {
        if(!isset($data[$field])){
            throw new Exception("Action require '$field' field", 1);
        }
    }
}

$config = parse_ini_file(__DIR__ . '/config.ini', true);
$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$pdo_db->query("SET NAMES utf8");

$anonActions = ['search', 'welcome', 'login', 'register'];

/*
 *      ACTIONS
 */

// return is success
// throw Exception is error
$actions = [
    'welcome' => function($data){
        return 'Welcome to opencorpora API v1.0!';
    },
    'search' => function($data){
        requireFields($data, ['query']);

        if (isset($data['all_forms'])) {
            $all_forms = (bool)$data['all_forms'];
        } else {
            $all_forms = false;
        }
        $answer['answer'] = get_search_results($data['query'], !$all_forms);
        foreach ($answer['answer']['results'] as &$res) {
            $parts = [];
            foreach (get_book_parents($res['book_id'], true) as $p) {
                $parts[] = $p['title'];
            }
            $res['text_fullname'] = join(': ', array_reverse($parts));
        }
        return $answer['answer'];
    },
    'login' => function($data){
        requireFields($data, ['login', 'password']);

        $user_id = user_check_password($data['login'], $data['password']);
        if ($user_id) {
            $token = remember_user($user_id, false, false);
            return [
                'token' => (string)$token,
                'user_id' => (int)$user_id,
            ];
        } else {
            throw new Exception("Incorrect login or password", 1);
        }
    },
    'register' => function($data){
        requireFields($data, ['login', 'passwd', 'passwd_re', 'email']);

        $reg_status = user_register($data);
        if ($reg_status == 1) {
            return 'User created';
        }
        throw new \Exception("User don't create: invalid data. Status:$reg_status", 1);
    },

    // require token
    'get_available_morph_tasks' => function($data){
        requireFields($data, ['user_id']);

        return get_available_tasks($data['user_id'], true);
    },
    'get_morph_task' => function($data){
        requireFields($data, ['user_id', 'pool_id', 'size']);

        return get_annotation_packet($data['pool_id'], $data['size'], $data['user_id']);
    },
    'save_morph_task' => function($data){
        requireFields($data, ['user_id', 'answers']);

        update_annot_instances($data['user_id'], $data['answers']);
        return 'save task success';
    },
];




/*
 *     COMMON API CHECKS
 */

if (!isset($_POST['action'])) {
    json(['error' => 'API required "action" field']);
}
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
$data   = isset($_POST['data']) ? json_decode($_POST['data'], true) : false;

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
