<?php
require_once('lib_mail.php');
function user_check_password($login, $password) {
    $password = md5(md5($password).substr($login, 0, 2));
    $r = sql_fetch_array(sql_query("SELECT `user_id` FROM `users` WHERE `user_name`='$login' AND `user_passwd`='$password' LIMIT 1"));
    if (!$r) return false;
    return $r['user_id'];
}
function is_valid_password($string) {
    return preg_match('/^[a-z0-9_-]+$/i', $string);
}
function user_generate_password($email) {
    $res = sql_query("SELECT user_id, user_name, user_passwd FROM `users` WHERE user_email='".mysql_real_escape_string($email)."' LIMIT 1");
    if (sql_num_rows($res) == 0) return 2;
    $r = sql_fetch_array($res);
    if ($r['user_passwd'] == '' || $r['user_passwd'] == 'notagreed')
        return 4;
    $pwd = gen_password();
    //send email
    if (send_email($email, 'Восстановление пароля на opencorpora.org', "Добрый день,\n\nВаш новый пароль для входа на opencorpora.org:\n\n$pwd\n\nРекомендуем как можно быстрее изменить его через интерфейс сайта.\n\nOpenCorpora")) {
        $md5 = md5(md5($pwd).substr($r['user_name'], 0, 2));
        if (sql_query("UPDATE `users` SET `user_passwd`='$md5' WHERE user_id=".$r['user_id']." LIMIT 1")) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return 3;
    }
}
function gen_password() {
    srand((double)microtime()*1000000);
    return uniqid(rand());
}
function is_valid_email($string) {
    return preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i', $string);
    //we took the regexp from regular-expressions.info
}
function check_auth_cookie() {
    list($user_id, $token) = explode('@', $_COOKIE['auth']);
    $res = sql_query("SELECT user_id FROM user_tokens WHERE user_id=".mysql_real_escape_string($user_id)." AND token=".$token." LIMIT 1");
    if (sql_num_rows($res) > 0) {
        $r = sql_fetch_array($res);
        return $r['user_id'];
    }
    return false;
}
function user_login($login, $passwd, $auth_user_id=0, $auth_token=0) {
    $login = mysql_real_escape_string($login);
    if (($user_id=$auth_user_id) || $user_id = user_check_password($login, $passwd)) {
        $alias_uid = check_for_user_alias($user_id);
        if ($alias_uid)
            $user_id = $alias_uid;
        sql_begin();
        $token = remember_user($user_id, $auth_token);
        if (!$token)
            return false;
        $r = sql_fetch_array(sql_query("SELECT user_shown_name, user_level, show_game FROM users WHERE user_id = $user_id LIMIT 1"));
        if (!init_session($user_id, $r['user_shown_name'], get_user_options($user_id), get_user_permissions($user_id),
                          $token, $r['user_level'], $r['show_game']))
            return false;
        sql_commit();
        return true;
    }
    return false;
}
function init_session($user_id, $user_name, $options, $permissions, $token, $level, $show_game) {
    if (!$options || !$permissions)
        return false;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $user_name;
    $_SESSION['options'] = $options;
    $_SESSION['user_permissions'] = $permissions;
    $_SESSION['token'] = $token;
    $_SESSION['user_level'] = $level;
    $_SESSION['show_game'] = $show_game;
    return true;
}
function check_for_user_alias($user_id) {
    // if a user tries to log in as alias_uid, he'll actually log in as primary_uid
    $res = sql_query("SELECT primary_uid FROM user_aliases WHERE alias_uid = $user_id LIMIT 1");
    if (sql_num_rows($res) > 0) {
        $r = sql_fetch_array($res);
        return $r['primary_uid'];
    }
    return false;
}
function remember_user($user_id, $auth_token=false) {
    //deleting the old token
    if ($auth_token) {
        sql_query("DELETE from user_tokens WHERE user_id=$user_id AND token='".mysql_real_escape_string(substr(strstr($auth_token, '@'), 1))."'");
    }
    //adding a new token
    $token = mt_rand();
    if (!sql_query("INSERT INTO user_tokens VALUES('$user_id','$token', '".time()."')", 1, 1))
        return false;

    setcookie('auth', $user_id.'@'.$token, time()+60*60*24*7, '/');
    return $token;
}
function user_login_openid($token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://loginza.ru/api/authinfo?token=$token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $arr = json_decode(curl_exec($ch), true);
    if (isset($arr['error_type'])) {
        print $arr['error_message'];
        return 0;
    }

    $id =  trim($arr['identity']);
    if (!$id)
        return false;
    //check if the user exists
    $res = sql_query("SELECT user_id FROM `users` WHERE user_name='$id' LIMIT 1");
    //if he doesn't
    if (sql_num_rows($res) == 0) {
        if (!sql_query("INSERT INTO `users` VALUES(NULL, '$id', 'notagreed', '', '".time()."', '$id', 0, 1, 1, 0, 0)")) {
            return 0;
        }
        $res = sql_query("SELECT user_id FROM `users` WHERE user_name='$id' LIMIT 1");
    }
    $row = sql_fetch_array($res);
    $user_id = $row['user_id'];
    $alias_uid = check_for_user_alias($user_id);
    if ($alias_uid)
        $user_id = $alias_uid;
    $token = remember_user($user_id, false);
    if (!$token)
        return false;
    $row = sql_fetch_array(sql_query("SELECT user_shown_name, user_passwd, user_level, show_game FROM users WHERE user_id = $user_id LIMIT 1"));
    if (!init_session($user_id, $row['user_shown_name'], get_user_options($user_id),
                      get_user_permissions($user_id), $token, $row['user_level'], $row['show_game']))
        return false;
    if ($row['user_passwd'] == 'notagreed') {
        $_SESSION['user_pending'] = 1;
        return 2;
    }
    return 1;
}
function user_login_openid_agree($agree) {
    if (!$agree)
        return false;
    unset($_SESSION['user_pending']);
    return (bool)sql_query("UPDATE users SET user_passwd='' WHERE user_id=".$_SESSION['user_id']." LIMIT 1");
}
function user_logout() {
    setcookie('auth', '', time()-1);
    if (!sql_query("DELETE FROM user_tokens WHERE user_id=".$_SESSION['user_id']." AND token='".$_SESSION['token']."'"))
        return false;
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_level']);
    unset($_SESSION['debug_mode']);
    unset($_SESSION['options']);
    unset($_SESSION['user_permissions']);
    unset($_SESSION['token']);
    unset($_SESSION['show_game']);
    return true;
}
function user_register($post) {
    $post['login'] = trim($post['login']);
    $post['email'] = strtolower(trim($post['email']));
    //testing if all fields are ok
    if ($post['passwd'] != $post['passwd_re']) 
        return 2;
    if ($post['passwd'] == '' || $post['login'] == '')
        return 5;
    if (!preg_match('/^[a-z0-9_-]+$/i', $post['login']))
        return 6;
    if (!is_valid_password($post['passwd']))
        return 7;
    if ($post['email'] && !is_valid_email($post['email']))
        return 8;
    //so far they are ok
    $name = mysql_real_escape_string($post['login']);
    $passwd = md5(md5($post['passwd']).substr($name, 0, 2));
    $email = mysql_real_escape_string($post['email']);
    if (sql_num_rows(sql_query("SELECT user_id FROM `users` WHERE user_name='$name' LIMIT 1")) > 0) {
        return 3;
    }
    if ($email && sql_num_rows(sql_query("SELECT user_id FROM `users` WHERE user_email='$email' LIMIT 1")) > 0) {
        return 4;
    }
    sql_begin();
    if (sql_query("INSERT INTO `users` VALUES(NULL, '$name', '$passwd', '$email', '".time()."', '$name', 0, 1, 1, 0, 0)")) {
        $user_id = sql_insert_id();
        if (!sql_query("INSERT INTO `user_permissions` VALUES ('$user_id', '0', '0', '0', '0', '0', '0', '0')")) return 0;
        if (isset($post['subscribe']) && $email) {
            //perhaps we should subscribe the user
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, "http://groups.google.com/group/opencorpora/boxsubscribe?email=$email");
            curl_exec($ch);
            curl_close($ch);
        }

        sql_commit();
        if (!user_login($name, $post['passwd']))
            return 0;
        return 1;
    }
    return 0;
}
function user_change_password($post) {
    //testing if the old password is correct
    $r = sql_fetch_array(sql_query("SELECT user_name FROM users WHERE user_id = ".$_SESSION['user_id']." LIMIT 1"));
    $login = $r['user_name'];
    if (user_check_password($login, $post['old_pw'])) {
        //testing if the two new passwords coincide
        if ($post['new_pw'] != $post['new_pw_re'])
            return 3;
        if (!preg_match('/^[a-z0-9_-]+$/i', $post['new_pw']))
            return 4;
        $passwd = md5(md5($post['new_pw']).substr($login, 0, 2));
        if (sql_query("UPDATE `users` SET `user_passwd`='$passwd' WHERE `user_id`=".$_SESSION['user_id']." LIMIT 1"))
            return 1;
        return 0;
    }
    else
        return 2;
}
function user_change_email($post) {
    $r = sql_fetch_array(sql_query("SELECT user_name FROM users WHERE user_id = ".$_SESSION['user_id']." LIMIT 1"));
    $login = $r['user_name'];
    $email = strtolower(trim($post['email']));
    if (is_user_openid($_SESSION['user_id']) || user_check_password($login, $post['passwd'])) {
        if (is_valid_email($email)) {
            $res = sql_query("SELECT user_id FROM users WHERE user_email='".mysql_real_escape_string($email)."' LIMIT 1");
            if (sql_num_rows($res) > 0) {
                return 4;
            }
            if (sql_query("UPDATE `users` SET `user_email`='".mysql_real_escape_string($email)."' WHERE `user_id`=".$_SESSION['user_id']." LIMIT 1"))
                return 1;
            return 0;
        } else
            return 3;
    }
    else
        return 2;
}
function user_change_shown_name($new_name) {
    $new_name = trim($new_name);
    if (!preg_match('/^[a-zа-я0-9ё_\-\s\.]{2,}$/ui', $new_name))
        return 2;
    if (sql_query("UPDATE users SET user_shown_name = '".mysql_real_escape_string($new_name)."' WHERE user_id = ".$_SESSION['user_id']." LIMIT 1")) {
        $_SESSION['user_name'] = $new_name;
        return 1;
    }
    return 0;
}
function get_user_info($user_id) {
    $res = sql_query("SELECT user_name, user_shown_name, user_reg FROM users WHERE user_id=$user_id LIMIT 1");
    $r = sql_fetch_array($res);
    $user = array(
        'name' => $r['user_name'],
        'shown_name' => $r['user_shown_name'],
        'registered' => $r['user_reg'],
        'total_answers' => 0,
        'checked_answers' => 0,
        'incorrect_answers' => 0
    );
    
    // annotation stats
    $annot = array();
    $last_type = '';
    $res = sql_query("
        SELECT pool_id, pool_name, p.status, type_id, t.grammemes, t.complexity, COUNT(instance_id) AS total, SUM(ms.answer != 0) AS checked,
            SUM(CASE WHEN (i.answer != ms.answer AND ms.answer > 0) THEN 1 ELSE 0 END) AS errors
        FROM morph_annot_instances i
        LEFT JOIN morph_annot_samples s USING (sample_id)
        LEFT JOIN morph_annot_pools p USING (pool_id)
        LEFT JOIN morph_annot_moderated_samples ms USING (sample_id)
        LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id)
        WHERE i.user_id = $user_id AND i.answer > 0
        GROUP BY pool_id
        ORDER BY type_id, pool_id
    ");

    $type = array();
    while ($r = sql_fetch_array($res)) {
        if ($r['type_id'] != $last_type) {
            if ($last_type)
                $annot[] = $type;
                $user['total_answers'] += $type['total_answers'];
                $user['checked_answers'] += $type['checked_answers'];
                $user['incorrect_answers'] += $type['incorrect_answers'];
            $type = array(
                'id' => $r['type_id'],
                'grammemes' => str_replace('@', ' / ', $r['grammemes']),
                'name' => preg_replace('/\s+#\d+\s*$/', '', $r['pool_name']),
                'complexity' => $r['complexity'],
                'pools' => array(),
                'total_answers' => 0,
                'checked_answers' => 0,
                'incorrect_answers' => 0
            );
        }
        $type['pools'][] = array(
            'id' => $r['pool_id'],
            'type' => $r['type_id'],
            'name' => $r['pool_name'],
            'status' => $r['status'],
            'total_answers' => $r['total'],
            'checked_answers' => $r['checked'],
            'incorrect_answers' => $r['errors']
        );
        $type['total_answers'] += $r['total'];
        $type['incorrect_answers'] += $r['errors'];
        $type['checked_answers'] += $r['checked'];
        $last_type = $r['type_id'];
    }
    $annot[] = $type;
    $user['total_answers'] += $type['total_answers'];
    $user['checked_answers'] += $type['checked_answers'];
    $user['incorrect_answers'] += $type['incorrect_answers'];

    $user['annot'] = $annot;
    return $user;
}
function get_user_email($user_id) {
    if (!$user_id) return;
    $res = sql_query("SELECT user_email FROM `users` WHERE user_id=$user_id LIMIT 1");
    if ($res) {
        $r = sql_fetch_array($res);
        return $r['user_email'];
    }
    return false;
}
function get_user_shown_name($user_id) {
    if (!$user_id) return;
    $res = sql_query("SELECT user_shown_name FROM `users` WHERE user_id=$user_id LIMIT 1");
    if ($res) {
        $r = sql_fetch_array($res);
        return $r['user_shown_name'];
    }
    return false;
}
function get_user_options($user_id) {
    if (!$user_id) return;
    $out = array();

    //autovivify
    $res = sql_query("SELECT option_id, default_value FROM user_options WHERE option_id NOT IN (SELECT option_id FROM user_options_values WHERE user_id=$user_id)");
    sql_begin();
    while ($r = sql_fetch_array($res)) {
        if (!sql_query("INSERT INTO user_options_values VALUES('$user_id', '".$r['option_id']."', '".$r['default_value']."')")) {
            return false;
        }
    }
    sql_commit();

    $res = sql_query("SELECT option_id id, option_value value FROM user_options_values WHERE user_id=$user_id");
    while ($r = sql_fetch_array($res))
        $out[$r['id']] = $r['value'];
    return $out;
}
function get_user_permissions($user_id) {
    if (!$user_id) return;
    $out = array();
    
    $res = sql_query("SELECT * FROM user_permissions WHERE user_id = $user_id LIMIT 1");

    if (sql_num_rows($res) == 0) {
        //autovivify
        if (!sql_query("INSERT INTO user_permissions VALUES ('$user_id', '0', '0', '0', '0', '0', '0', '0')")) {
            return false;
        }
        $res = sql_query("SELECT * FROM user_permissions WHERE user_id = $user_id LIMIT 1");
    }

    $r = sql_fetch_assoc($res);
    foreach ($r as $column_name => $val) {
        if ($column_name == 'user_id') continue;
        $out[$column_name] = $val;
    }

    return $out;
}
function get_meta_options() {
    $out = array();
    $res = sql_query("SELECT * FROM user_options WHERE option_id != 2 ORDER BY `order_by`");
    while ($r = sql_fetch_array($res)) {
        if ($r['option_values'] == '1') {
            $out[$r['option_id']] = array('name'=>$r['option_name'], 'value_type'=>$r['option_values']);
        } else {
            $values = array();
            foreach (explode('|', $r['option_values']) as $t) {
                list($val, $descr) = explode('=', $t);
                $values[$val] = $descr;
            }
            $out[$r['option_id']] = array('name'=>$r['option_name'], 'value_type'=>2, 'values' => $values);
        }
    }
    return $out;
}
function save_user_options($post) {
    if (!isset($post['options'])) {
        return false;
    }
    sql_begin();
    foreach ($post['options'] as $id=>$value) {
        if ($_SESSION['options'][$id]['value'] != $value) {
            if (!sql_query("UPDATE user_options_values SET option_value='".mysql_real_escape_string($value)."' WHERE option_id=".mysql_real_escape_string($id)." AND user_id=".$_SESSION['user_id']." LIMIT 1")) {
                return false;
            }
            $_SESSION['options'][$id] = mysql_real_escape_string($value);
        }
    }
    sql_commit();
    return true;
}
function is_admin() {
    return (
        isset($_SESSION['user_permissions']['perm_admin']) &&
        $_SESSION['user_permissions']['perm_admin'] == 1 &&
        !isset($_SESSION['user_permissions']['pretend'])
    );
}
function is_logged() {
    return (isset($_SESSION['user_id']) && $_SESSION['user_id']>0 && !isset($_SESSION['user_pending']));
}
function is_user_openid($user_id) {
    $r = sql_fetch_array(sql_query("SELECT user_passwd FROM users WHERE user_id=$user_id LIMIT 1"));
    return ($r['user_passwd'] == '' || $r['user_passwd'] == 'notagreed');
}
function user_has_permission($perm) {
    return (
        is_admin() ||
        (is_logged() && isset($_SESSION['user_permissions'][$perm]) &&
        $_SESSION['user_permissions'][$perm] == 1)
    );
}
function get_users_page() {
    $res = sql_query("SELECT p.*, u.user_id, user_shown_name AS user_name, user_reg, user_email, show_game FROM users u LEFT JOIN user_permissions p ON (u.user_id = p.user_id)");
    $out = array();
    while ($r = sql_fetch_assoc($res)) {
        $out[] = $r;
    }
    return $out;
}
function save_users($post) {
    include_once('lib_awards.php');
    sql_begin();
    $game = $post['game'];
    foreach ($post['changed'] as $id => $val) {
        if (!$val) continue;
        $perm = $post['perm'][$id];
        $qa = array();
        if (isset($perm['admin'])) $qa[] = "perm_admin='1'";
        if (isset($perm['adder'])) $qa[] = "perm_adder='1'";
            else $qa[] = "perm_adder='0'";
        if (isset($perm['dict'])) $qa[] = "perm_dict='1'";
            else $qa[] = "perm_dict='0'";
        if (isset($perm['disamb'])) $qa[] = "perm_disamb='1'";
            else $qa[] = "perm_disamb='0'";
        if (isset($perm['tokens'])) $qa[] = "perm_check_tokens='1'";
            else $qa[] = "perm_check_tokens='0'";
        if (isset($perm['morph'])) $qa[] = "perm_check_morph='1'";
            else $qa[] = "perm_check_morph='0'";
        if (isset($perm['merge'])) $qa[] = "perm_merge='1'";
            else $qa[] = "perm_merge='0'";

        $q = "UPDATE user_permissions SET ".implode(', ', $qa)." WHERE user_id=$id LIMIT 1";
        if (!sql_query($q) || !sql_query("DELETE FROM user_tokens WHERE user_id=$id")) {
            return false;
        }
        // game part
        if (isset($game[$id])) {
            if (!turn_game_on($id))
                return false;
        } else {
            if (!turn_game_off($id))
                return false;
        }
    }
    sql_commit();
    return true;
}
function get_team_list() {
    $out = array();
    $res = sql_query("SELECT team_id, team_name, COUNT(user_id) AS num_users FROM user_teams t LEFT JOIN users u ON (t.team_id = u.user_team) GROUP BY team_id");
    while ($r = sql_fetch_array($res)) {
        $out[$r['team_id']] = array(
            'name' => $r['team_name'],
            'num_users' => $r['num_users']
        );
    }
    return $out;
}
function save_user_team($team_id, $new_team_name=false) {
    if (!$_SESSION['user_id'])
        return false;
    // create new team if necessary
    sql_begin();
    if ($new_team_name) {
        if (!sql_query("INSERT INTO user_teams VALUES(NULL, '".mysql_real_escape_string($new_team_name)."', ".$_SESSION['user_id'].")"))
            return false;
        $team_id = sql_insert_id();
    }

    if (sql_query("UPDATE users SET user_team=$team_id WHERE user_id=".$_SESSION['user_id']." LIMIT 1")) {
        sql_commit();
        return $team_id;
    }
    return false;
}
function get_user_team($user_id) {
    $res = sql_query("SELECT user_team, team_id, team_name FROM users LEFT JOIN user_teams ON (user_team=team_id) WHERE user_id=$user_id LIMIT 1");
    return sql_fetch_array($res);
}
?>
