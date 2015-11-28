<?php

require_once('lib/common.php');
require_once("lib/lib_users.php");
require_once("lib/lib_achievements.php");
require_once("lib/lib_annot.php");

function json($data) {
    header('Content-type: application/json');
    echo json_encode($data);
    die();
}

function require_fields($data, $fields)
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

$anonActions = ['search', 'welcome', 'login', 'register', 'all_stat'];

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
        require_fields($data, ['query']);

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
        require_fields($data, ['login', 'password']);

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
        require_fields($data, ['login', 'passwd', 'passwd_re', 'email']);

        $reg_status = user_register($data);
        if ($reg_status == 1) {
            return 'User created';
        }
        throw new \Exception("User don't create: invalid data. Status:$reg_status", 1);
    },
    'all_stat' => function($data){
        return get_user_stats(true, false);
    },

    // require token
    'get_available_morph_tasks' => function($data){
        require_fields($data, ['user_id']);

        return get_available_tasks($data['user_id'], true);
    },
    'get_morph_task' => function($data){
        require_fields($data, ['user_id', 'pool_id', 'size']);

        return get_annotation_packet($data['pool_id'], $data['size'], $data['user_id']);
    },
    'save_morph_task' => function($data){
        require_fields($data, ['user_id', 'answers']);

        update_annot_instances($data['user_id'], $data['answers']);
        return 'save task success';
    },

    'get_user' => function($data){
        require_fields($data, ['user_id']);

        $mgr = new UserOptionsManager();
        return [
            'options' => $mgr->get_all_options(true),
            'current_email' => get_user_email($data['user_id']),
            'current_name' => get_user_shown_name($data['user_id']),
            'user_team' => get_user_team($data['user_id']),
        ];

    },
    'save_user' => function($data){
        // update:
        // shown_name OR email (+ passwd user_id) OR passwd (+old_passwd user_id)
        if(isset($data['shown_name'])) {
            if(user_change_shown_name($data['shown_name']) !== 1){
                throw new Exception("Error update 'shown_name' field", 1);
            }
        }

        if(isset($data['email']) && isset($data['passwd']) && isset($data['user_id'])) {
            // NOTE: hotpatch
            $r = sql_fetch_array(sql_query("SELECT user_name FROM users WHERE user_id = ".$data['user_id']." LIMIT 1"));
            $login = $r['user_name'];
            $email = strtolower(trim($data['email']));
            if (is_user_openid($data['user_id']) || user_check_password($login, $data['passwd'])) {
                if (is_valid_email($email)) {
                    $res = sql_pe("SELECT user_id FROM users WHERE user_email=? LIMIT 1", array($email));
                    if (sizeof($res) > 0) {
                        throw new Exception("Error update 'email' field", 1);
                    }
                    sql_pe("UPDATE `users` SET `user_email`=? WHERE `user_id`=? LIMIT 1", array($email, $data['user_id']));
                } else {
                    throw new Exception("Error update 'email' field", 1);
                }
            } else {
                throw new Exception("Error update 'email' field", 1);
            }
        }

        if(isset($data['user_id']) && isset($data['passwd']) && isset($data['old_passwd'])) {
            // NOTE: hotpatch
            $r = sql_fetch_array(sql_query("SELECT user_name FROM users WHERE user_id = ".$data['user_id']." LIMIT 1"));
            $login = $r['user_name'];
            if (user_check_password($login, $data['old_passwd'])) {
                if (!is_valid_password($data['passwd'])){
                    throw new Exception("Error update 'passwd' field", 1);
                }
                $passwd = md5(md5($data['passwd']).substr($login, 0, 2));
                sql_query("UPDATE `users` SET `user_passwd`='$passwd' WHERE `user_id`=".$data['user_id']." LIMIT 1");
            } else {
                throw new Exception("Error update 'passwd' field", 1);
            }
        }
        return 'update user success';
    },

    'user_stat' => function($data){
        require_fields($data, ['user_id']);

        return get_user_info($data['user_id']);
    },
    'grab_badges' => function($data){
        require_fields($data, ['user_id']);

        $am2 = new AchievementsManager($data['user_id']);
        return $am2->pull_all();
    },
];

// action list
// var_dump(array_keys($actions)); die();



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
    $user_id = check_auth_token($token);
    if (!$user_id) {
        json(['error' => 'Incorrect token']);
    }
}

// action REQUIRE, data OPTIONAL
$action = $_POST['action'];
$data   = isset($_POST['data']) ? json_decode($_POST['data'], true) : null;

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
