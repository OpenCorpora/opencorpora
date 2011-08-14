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
    $res = sql_query("SELECT user_id, user_name FROM `users` WHERE user_email='".mysql_real_escape_string($email)."' LIMIT 1");
    if (sql_num_rows($res) == 0) return 2;
    $r = sql_fetch_array($res);
    $pwd = gen_password();
    //send email
    if (send_email($email, 'Восстановление пароля на opencorpora.org', "Добрый день,\n\nВаш новый пароль для входа на opencorpora.org:\n\n$pwd\n\nРекомендуем как можно быстрее изменить его через интерфейс сайта.\n\nOpenCorpora")) {
        $md5 = md5(md5($pwd).substr($r['user_name'], 0, 2));
        if (sql_query("UPDATE `users` SET `user_passwd`='$md5' WHERE user_id=".$r['user_id']." LIMIT 1")) {
            return 1;
        } else {
            return 4;
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
}
function user_login($login, $passwd, $auth_user_id=0, $auth_token=0) {
    $login = mysql_real_escape_string($login);
    if (($user_id=$auth_user_id) || $user_id = user_check_password($login, $passwd)) {
        //deleting the old token
        if ($auth_token) {
            sql_query("DELETE from user_tokens WHERE user_id=$user_id AND token='".mysql_real_escape_string(substr(strstr($auth_token, '@'), 1))."'");
            $r = sql_fetch_array(sql_query("SELECT user_name FROM users WHERE user_id=$user_id LIMIT 1"));
            $login=$r['user_name'];
        }
        //adding a new token
        $token = mt_rand();
        if (!sql_query("INSERT INTO user_tokens VALUES('$user_id','$token', '".time()."')", 1, 1))
            return false;
        setcookie('auth', $user_id.'@'.$token, time()+60*60*24*7, '/');
        //setting the session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $login;
        $_SESSION['options'] = get_user_options($user_id);
        $_SESSION['user_permissions'] = get_user_permissions($user_id);
        $_SESSION['token'] = $token; //we may need to delete it on logout
        return true;
    }
    return false;
}
function user_login_openid($token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://loginza.ru/api/authinfo?token=$token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $arr = json_decode(curl_exec($ch), true);
    if (isset($arr['error_type'])) {
        print ($arr['error_message']);
        return 0;
    }
    $id = '';
    if (strpos($arr['provider'], 'google') !== false)
        $id = 'google:'.$arr['uid'];
    else
        $id =  $arr['identity'];
    //check if the user exists
    $res = sql_query("SELECT user_id, user_passwd FROM `users` WHERE user_name='$id' LIMIT 1");
    //if he doesn't
    if (sql_num_rows($res) == 0) {
        if (!sql_query("INSERT INTO `users` VALUES(NULL, '$id', 'notagreed', '', '".time()."')")) {
            return 0;
        }
        $res = sql_query("SELECT user_id, user_passwd FROM `users` WHERE user_name='$id' LIMIT 1");
    }
    $row = sql_fetch_array($res);
    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['user_name'] = $id;
    $_SESSION['options'] = get_user_options($row['user_id']);
    $_SESSION['user_permissions'] = get_user_permissions($row['user_id']);
    if ($row['user_passwd'] == 'notagreed') {
        $_SESSION['user_pending'] = 1;
        return 2;
    }
    return 1;
}
function user_login_openid_agree($agree) {
    if ($agree) {
        unset($_SESSION['user_pending']);
        sql_query("UPDATE users SET user_passwd='' WHERE user_id=".$_SESSION['user_id']." LIMIT 1");
        header('Location:'.$_SESSION['return_to']);
    }
}
function user_logout() {
    setcookie('auth', '', time()-1);
    sql_query("DELETE FROM user_tokens WHERE user_id=".$_SESSION['user_id']." AND token='".$_SESSION['token']."'");
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['debug_mode']);
    unset($_SESSION['options']);
    unset($_SESSION['user_permissions']);
    unset($_SESSION['token']);
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
    if (sql_query("INSERT INTO `users` VALUES(NULL, '$name', '$passwd', '$email', '".time()."')"))
        return 1;
    return 0;
}
function user_change_password($post) {
    //testing if the old password is correct
    $login = $_SESSION['user_name'];
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
    $login = $_SESSION['user_name'];
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
function get_user_email($user_id) {
    if (!$user_id) return;
    $res = sql_query("SELECT user_email FROM `users` WHERE user_id=$user_id LIMIT 1");
    if ($res) {
        $r = sql_fetch_array($res);
        return $r['user_email'];
    }
    return false;
}
function get_user_options($user_id) {
    if (!$user_id) return;
    $out = array();

    //autovivify
    $res = sql_query("SELECT option_id, default_value FROM user_options WHERE option_id NOT IN (SELECT option_id FROM user_options_values WHERE user_id=$user_id)");
    while($r = sql_fetch_array($res)) {
        if (!sql_query("INSERT INTO user_options_values VALUES('$user_id', '".$r['option_id']."', '".$r['default_value']."')")) {
            show_error("Error on autovivifying an option");
            return;
        }
    }

    $res = sql_query("SELECT option_id id, option_value value FROM user_options_values WHERE user_id=$user_id");
    while($r = sql_fetch_array($res))
        $out[$r['id']] = $r['value'];
    return $out;
}
function get_user_permissions($user_id) {
    if (!$user_id) return;
    $out = array();
    
    $res = sql_query("SELECT * FROM user_permissions WHERE user_id = $user_id LIMIT 1");
    if ($r = sql_fetch_assoc($res)) {
        foreach ($r as $column_name => $val) {
            if ($column_name == 'user_id') continue;
            $out[$column_name] = $val;
        }
    } else {
        //autovivify
        if (!sql_query("INSERT INTO user_permissions VALUES ('$user_id', '0', '0', '0', '0', '0', '0')")) {
            show_error();
            return;
        }
    }

    return $out;
}
function get_meta_options() {
    $out = array();
    $res = sql_query("SELECT * FROM user_options ORDER BY `order_by`");
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
        header('Location:options.php');
        return;
    }
    foreach($post['options'] as $id=>$value) {
        if($_SESSION['options'][$id]['value'] != $value) {
            if(!sql_query("UPDATE user_options_values SET option_value='".mysql_real_escape_string($value)."' WHERE option_id=".mysql_real_escape_string($id)." AND user_id=".$_SESSION['user_id']." LIMIT 1")) {
                show_error("Error on saving options");
                return;
            }
            $_SESSION['options'][$id] = mysql_real_escape_string($value);
        }
    }
    header('Location:options.php?saved=1');
    return;
}
function is_admin() {
    return (
        isset($_SESSION['user_permissions']['perm_admin']) &&
        $_SESSION['user_permissions']['perm_admin'] == 1 &&
        !$_SESSION['user_permissions']['pretend']
    );
}
function is_logged() {
    return (isset($_SESSION['user_id']) && $_SESSION['user_id']>0 && !isset($_SESSION['user_pending']));
}
function is_user_openid($user_id) {
    $r = sql_fetch_array(sql_query("SELECT user_passwd WHERE user_id=$user_id LIMIT 1"));
    return $r['user_passwd'] === '';
}
function user_has_permission($perm) {
    return (
        is_admin() ||
        (isset($_SESSION['user_permissions'][$perm]) &&
        $_SESSION['user_permissions'][$perm] == 1)
    );
}
function get_users_page() {
    $res = sql_query("SELECT p.*, u.user_id, user_name, user_reg, user_email FROM users u LEFT JOIN user_permissions p ON (u.user_id = p.user_id)");
    $out = array();
    while ($r = sql_fetch_assoc($res)) {
        $out[] = $r;
    }
    return $out;
}
function save_users($post) {
    foreach($post['changed'] as $id => $val) {
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

        $q = "UPDATE user_permissions SET ".implode(', ', $qa)." WHERE user_id=$id LIMIT 1";
        if (!sql_query($q)) {
            show_error();
            return;
        }
    }
    header("Location:users.php");
}
?>
