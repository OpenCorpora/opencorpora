<?php
function user_login($login, $passwd) {
    $login = mysql_real_escape_string($login);
    $passwd = md5(md5($passwd).substr($login, 0, 2));
    $q = sql_query("SELECT `user_id`, `user_group`  FROM `users` WHERE `user_name`='$login' AND `user_passwd`='$passwd'");
    if ($row = sql_fetch_array($q)) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_group'] = $row['user_group'];
        $_SESSION['user_name'] = $login;
        $_SESSION['options'] = get_user_options($row['user_id']);
        return true;
    } else {
        return false;
    }
}
function user_logout() {
    unset ($_SESSION['user_id']);
    unset ($_SESSION['user_group']);
    unset ($_SESSION['user_name']);
    unset ($_SESSION['debug_mode']);
    unset ($_SESSION['options']);
}
function user_register($post) {
    if ($post['passwd'] != $post['passwd_re']) 
        return 2;
    if ($post['passwd'] == '' || $post['login'] == '')
        return 5;
    $name = mysql_real_escape_string($post['login']);
    $passwd = md5(md5($post['passwd']).substr($name, 0, 2));
    $email = mysql_real_escape_string($post['email']);
    if (sql_num_rows(sql_query("SELECT user_id FROM `users` WHERE user_name='$name' LIMIT 1")) > 0) {
        return 3;
    }
    if (sql_num_rows(sql_query("SELECT user_id FROM `users` WHERE user_email='$email' LIMIT 1")) > 0) {
        return 4;
    }
    if (sql_query("INSERT INTO `users` VALUES(NULL, '$name', '$passwd', '1', '$email', '".time()."')"))
        return 1;
    return 0;
}
function user_pretend($act) {
    if ($_SESSION['user_group'] < 6) return 0;
    if ($act == 0)
        $_SESSION['user_group'] = 7;
    else
        $_SESSION['user_group'] = 6;
    return 1;
}
function get_user_options($user_id) {
    if (!$user_id) return;
    $out = array();
    $res = sql_query("SELECT t.option_id AS `id`, t.option_name AS `name`, o.option_value AS `value`, t.option_values AS `values` FROM user_options o LEFT JOIN user_options_types t ON (t.option_id=o.option_id) WHERE o.user_id=$user_id");
    while($r = sql_fetch_array($res)) {
        $out[$r['id']] = array('value'=>$r['value'], 'name'=>$r['name'], 'value_type'=>$r['values']);
        if (strpos($r['values'], '|')) {
            $out[$r['id']]['value_type'] = explode('|', $r['values']);
        }
    }
    return $out;
}
function save_user_options($post) {
    foreach($post['options'] as $id=>$value) {
        if($_SESSION['options'][$id]['value'] != $value) {
            if(!sql_query("UPDATE user_options SET option_value='".mysql_real_escape_string($value)."' WHERE option_id=".mysql_real_escape_string($id)." AND user_id=".$_SESSION['user_id']." LIMIT 1")) {
                show_error("Error on saving options");
                return;
            }
            $_SESSION['options'][$id]['value'] = $value;
        }
    }
    header('Location:options.php?saved=1');
}
function is_admin() {
    return (isset($_SESSION['user_group']) && $_SESSION['user_group']==7);
}
function is_logged() {
    return (isset($_SESSION['user_id']) && $_SESSION['user_id']>0);
}
?>
