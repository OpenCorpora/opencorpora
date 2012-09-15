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
    return false;
}
function user_login($login, $passwd, $auth_user_id=0, $auth_token=0) {
    $login = mysql_real_escape_string($login);
    if (($user_id=$auth_user_id) || $user_id = user_check_password($login, $passwd)) {
        sql_begin();
        //deleting the old token
        if ($auth_token) {
            sql_query("DELETE from user_tokens WHERE user_id=$user_id AND token='".mysql_real_escape_string(substr(strstr($auth_token, '@'), 1))."'");
            $r = sql_fetch_array(sql_query("SELECT user_shown_name AS user_name FROM users WHERE user_id=$user_id LIMIT 1"));
            $login=$r['user_name'];
        }
        //adding a new token
        $token = mt_rand();
        if (!sql_query("INSERT INTO user_tokens VALUES('$user_id','$token', '".time()."')", 1, 1))
            return false;
        setcookie('auth', $user_id.'@'.$token, time()+60*60*24*7, '/');
        //setting the session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = get_user_shown_name($user_id);
        $_SESSION['options'] = get_user_options($user_id);
        $_SESSION['user_permissions'] = get_user_permissions($user_id);
        if (!$_SESSION['options'] || !$_SESSION['user_permissions'])
            return false;
        $_SESSION['token'] = $token; //we may need to delete it on logout
        sql_commit();
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
        print $arr['error_message'];
        return 0;
    }
    $id = '';
    if (strpos($arr['provider'], 'google') !== false)
        $id = 'google:'.$arr['uid'];
    else
        $id =  $arr['identity'];
    //check if the user exists
    $res = sql_query("SELECT user_id, user_passwd, user_shown_name AS user_name FROM `users` WHERE user_name='$id' LIMIT 1");
    //if he doesn't
    if (sql_num_rows($res) == 0) {
        if (!sql_query("INSERT INTO `users` VALUES(NULL, '$id', 'notagreed', '', '".time()."', '$id')")) {
            return 0;
        }
        $res = sql_query("SELECT user_id, user_passwd, user_shown_name AS user_name FROM `users` WHERE user_name='$id' LIMIT 1");
    }
    $row = sql_fetch_array($res);
    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['user_name'] = get_user_shown_name($row['user_id']);
    $_SESSION['options'] = get_user_options($row['user_id']);
    $_SESSION['user_permissions'] = get_user_permissions($row['user_id']);
    if (!$_SESSION['options'] || !$_SESSION['user_permissions'])
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
    unset($_SESSION['debug_mode']);
    unset($_SESSION['options']);
    unset($_SESSION['user_permissions']);
    unset($_SESSION['token']);
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
    if (sql_query("INSERT INTO `users` VALUES(NULL, '$name', '$passwd', '$email', '".time()."', '$name')")) {
        $user_id = sql_insert_id();
        if (!sql_query("INSERT INTO `user_permissions` VALUES ('$user_id', '0', '0', '0', '0', '0', '0')")) return 0;
        if (isset($post['subscribe']) && $email) {
            //perhaps we should subscribe the user
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, "http://groups.google.com/group/opencorpora/boxsubscribe?email=$email");
            curl_exec($ch);
            curl_close($ch);
        }

        sql_commit();
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
        if (!sql_query("INSERT INTO user_permissions VALUES ('$user_id', '0', '0', '0', '0', '0', '0')")) {
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
    $res = sql_query("SELECT p.*, u.user_id, user_shown_name AS user_name, user_reg, user_email FROM users u LEFT JOIN user_permissions p ON (u.user_id = p.user_id)");
    $out = array();
    while ($r = sql_fetch_assoc($res)) {
        $out[] = $r;
    }
    return $out;
}
function save_users($post) {
    sql_begin();
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

        $q = "UPDATE user_permissions SET ".implode(', ', $qa)." WHERE user_id=$id LIMIT 1";
        if (!sql_query($q)) {
            return false;
        }
    }
    sql_commit();
    return true;
}
function get_user_badges($user_id, $only_shown=true) {
    $only_shown_str = $only_shown ? "AND shown > 0" : '';
    $out = array();
    $res = sql_query("SELECT t.badge_id, t.badge_name, t.badge_descr, t.badge_image, b.shown
                        FROM user_badges b
                        LEFT JOIN user_badges_types t USING (badge_id)
                        WHERE user_id=$user_id $only_shown_str");
    while ($r = sql_fetch_array($res)) {
        $out[] = array(
            'id' => $r['badge_id'],
            'name' => $r['badge_name'],
            'description' => $r['badge_descr'],
            'image_name' => $r['badge_image'],
            'shown_time' => $r['shown']
        );
    }
    return $out;
}
function mark_shown_badge($user_id, $badge_id) {
    if (sql_query("UPDATE user_badges SET shown=".time()." WHERE user_id=$user_id AND badge_id=$badge_id LIMIT 1"))
        return true;
    return false;
}
function check_user_simple_badges($user_id) {
    global $config;
    $thresholds = explode(',', $config['badges']['simple']);
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt FROM morph_annot_instances WHERE user_id = $user_id AND answer > 0"));
    $count = $r['cnt'];
    $res = sql_query("SELECT MAX(badge_id) AS max_badge FROM user_badges WHERE user_id = $user_id AND badge_id <= 20");
    if (sql_num_rows($res) == 0)
        $max_badge = 0;
    else {
        $r = sql_fetch_array($res);
        $max_badge = $r['max_badge'];
    }

    foreach ($thresholds as $i => $thr) {
        if ($max_badge > $i)
            continue;
        if ($count < $thr)
            break;
        // user should get a badge!
        $badge_id = $i + 1;
        if (sql_query("INSERT INTO user_badges VALUES($user_id, $badge_id, 0)"))
            return $badge_id;
        break;
    }
    return false;
}
?>
