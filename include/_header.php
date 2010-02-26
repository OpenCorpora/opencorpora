<div id='header'>
<div id='lblock'><a href='<?=$config['web_prefix']?>/'>Home</a></div>
<div id='rblock'>
<?php
if (isset($_SESSION['user_id'])) {
    print "Вы &ndash; <b>".$_SESSION['user_name']."</b>";
    if (is_admin()) {
        print ", администратор";
    }
    print " [<a href='".$config['web_prefix']."/login.php?act=logout'>выйти</a>]";
} else {
    print "<a href='".$config['web_prefix']."/login.php'>Вход/Регистрация</a>";
}
?>
</div>
</div>
