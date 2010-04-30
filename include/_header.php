<?php
if (stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0') !== false)
    print "<div id='pre_header'>Вы используете устаревший браузер Internet Explorer 6, который может некорректно отображать наш сайт. Рекомендуем обновить или поменять браузер.</div>\n";
?>
<div id='header'>
<div id='lblock'><a href='<?=$config['web_prefix']?>/'>Home</a></div>
<div id='rblock'>
<?php
if (isset($_SESSION['user_id'])) {
    print "Вы &ndash; <b>".$_SESSION['user_name']."</b>";
    if (is_admin()) {
        print ", администратор";
        if (isset($_SESSION['debug_mode'])) {
            print " [<a href='?debug=off'>debug off</a>]";
        } else {
            print " [<a href='?debug=on'>debug on</a>]";
        }
    }
    print " [<a href='".$config['web_prefix']."/login.php?act=logout'>выйти</a>]";
} else {
    print "<a href='".$config['web_prefix']."/login.php'>Вход/Регистрация</a>";
}
?>
</div>
</div>
