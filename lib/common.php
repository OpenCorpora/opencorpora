<?php
#sql wrappers
function sql_query($q, $debug=1) {
    $res = mysql_query($q);
    if (isset($_SESSION['debug_mode']) && $debug) {
        print "<span class='debug'>SQL: ".htmlspecialchars($q)."</span><br/>\n";
        if ($err = mysql_error()) {
            print "<span class='debug_error'>$err</span><br/>\n";
        }
    }
    return $res;
}
function sql_fetch_array($q) {
    return mysql_fetch_array($q);
}
function sql_num_rows($q) {
    return mysql_num_rows($q);
}
#other
function user_login($login, $passwd) {
    $passwd = md5(md5($passwd).substr($login, 0, 2));
    $q = sql_query("SELECT `user_id`, `user_group`  FROM `users` WHERE `user_name`='$login' AND `user_passwd`='$passwd'");
    if ($row = sql_fetch_array($q)) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_group'] = $row['user_group'];
        $_SESSION['user_name'] = $login;
        return true;
    } else {
        return false;
    }
}
function user_logout() {
    unset ($_SESSION['user_id']);
    unset ($_SESSION['user_group']);
    unset ($_SESSION['user_name']);
}
function is_admin() {
    return $_SESSION['user_group']==7;
}
?>
