<div id='header'>
<?php
if (isset($_SESSION['user_id'])) {
    print "Вы &ndash; <b>".$_SESSION['user_name']."</b>";
    if (is_admin()) {
        print ", администратор";
    }
    print " [<a href='login.php?act=logout'>выйти</a>]";
} else {
    print "<a href='login.php'>Вход/Регистрация</a>";
}
?>
</div>