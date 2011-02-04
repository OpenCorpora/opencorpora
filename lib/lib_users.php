<?php
function user_check_password($login, $password) {
    $password = md5(md5($password).substr($login, 0, 2));
    $q = sql_query("SELECT `user_id`, `user_group`  FROM `users` WHERE `user_name`='$login' AND `user_passwd`='$password' LIMIT 1");
    return sql_fetch_array($q);
}
function is_valid_password($string) {
    return preg_match('/^[a-z0-9_-]+$/i', $string);
}
function is_valid_email($string) {
    return preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i', $string);
    //we took the regexp from regular-expressions.info
}
function user_login($login, $passwd) {
    $login = mysql_real_escape_string($login);
    if ($row = user_check_password($login, $passwd)) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_group'] = $row['user_group'];
        $_SESSION['user_name'] = $login;
        $_SESSION['options'] = get_user_options($row['user_id']);
        return true;
    }
    return false;
}
function user_logout() {
    unset ($_SESSION['user_id']);
    unset ($_SESSION['user_group']);
    unset ($_SESSION['user_name']);
    unset ($_SESSION['debug_mode']);
    unset ($_SESSION['options']);
}
function user_register($post) {
    $post['login'] = trim($post['login']);
    $post['email'] = trim($post['email']);
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
    if (sql_query("INSERT INTO `users` VALUES(NULL, '$name', '$passwd', '1', '$email', '".time()."')"))
        return 1;
    return 0;
}
function user_change_password($post) {
    //testing if the old password is correct
    $login = $_SESSION['user_name'];
    if ($row = user_check_password($login, $post['old_pw'])) {
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
    if ($row = user_check_password($login, $post['passwd'])) {
        if (is_valid_email($post['email'])) {
            if (sql_query("UPDATE `users` SET `user_email`='".mysql_real_escape_string($post['email'])."' WHERE `user_id`=".$_SESSION['user_id']." LIMIT 1"))
                return 1;
            return 0;
        } else
            return 3;
    }
    else
        return 2;
}
function user_pretend($act) {
    if ($_SESSION['user_group'] < 6) return 0;
    if ($act == 0)
        $_SESSION['user_group'] = 7;
    else
        $_SESSION['user_group'] = 6;
    return 1;
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
function get_meta_options() {
    $out = array();
    $res = sql_query("SELECT * FROM user_options ORDER BY `order_by`");
    while ($r = sql_fetch_array($res)) {
        $out[$r['option_id']] = array('name'=>$r['option_name'], 'value_type'=>$r['option_values']);
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
            $_SESSION['options'][$id] = $value;
        }
    }
    header('Location:options.php?saved=1');
    return;
}
function is_admin() {
    return (isset($_SESSION['user_group']) && $_SESSION['user_group']==7);
}
function is_logged() {
    return (isset($_SESSION['user_id']) && $_SESSION['user_id']>0);
}
?>
