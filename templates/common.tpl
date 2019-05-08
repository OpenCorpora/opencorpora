<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv='content' content='text/html;charset=utf-8'/>
        <title>OpenCorpora: открытый корпус русского языка</title>

        <meta property="og:image" content="http://opencorpora.org/assets/img/fb-pic.png"/>
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://opencorpora.org" />
        <meta property="og:title" content="OpenCorpora: открытый корпус русского языка" />

        <link rel="shortcut icon" href="/favicon.ico" />

        <!-- Bootstrap -->
        <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="/assets/css/btn-palette.css" rel="stylesheet">

        <link rel="stylesheet" type="text/css" href="/assets/css/main.css?12"/>

        <!-- Open Sans for headers -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,800,700&subset=latin,cyrillic" rel="stylesheet" type="text/css">

        {block name=styles}{/block}

        <script src="/assets/vendor/jquery/jquery.min.js"></script>
        <script src="/assets/js/main.js?3" type="text/javascript"></script>
        <script src="/assets/js/jquery.mousewheel.js" type="text/javascript"></script>
        <script src="/assets/vendor/bootstrap/js/bootstrap.min.js"></script>
        <script src="/vendor/mouse0270/bootstrap-notify/bootstrap-notify.min.js"></script>

    </head>
{block name=nojs}<noscript><meta http-equiv="refresh" content="0; URL=/no_js.php"></noscript>{/block}
{block name=body}<body>{/block}
<div id='wrap'>
{nocache}{include file='header.tpl'}
{block name=before_content}{/block}{/nocache}
{if $readonly == 1}
<div class='alert alert-error'><div class="container">Система находится в режиме &laquo;только для чтения&raquo;. Функции разметки и редактирования временно не работают.</div></div>
{/if}
<div id="container" class="container">
{if $game_is_on == 1}{include file='qa/user_splash.tpl' achievements_titles=$achievements_titles}{/if}
<div id="alert_wrap">{if $alerts}{foreach $alerts as $type=>$message}<div class="alert alert-{$type}">{$message}</div>{/foreach}<script>setTimeout('$("#alert_wrap .alert").fadeOut()',3000);</script>{/if}
</div>
{block name=content}{/block}
</div>
<div class="notifications top-right"></div>
{include file='footer.tpl'}
</div>
<script type="text/javascript">

var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

if (w > 480) {
    var reformalOptions = {
        project_id: 93183,
        project_host: "opencorpora.reformal.ru",
        tab_orientation: "right",
        tab_indent: "30%",
        tab_bg_color: "#2852a6",
        tab_border_color: "#FFFFFF",
        tab_image_url: "http://tab.reformal.ru/T9GC0LfRi9Cy0Ysg0Lgg0L%252FRgNC10LTQu9C%252B0LbQtdC90LjRjw==/FFFFFF/2a94cfe6511106e7a48d0af3904e3090/left/1/tab.png",
        tab_border_width: 2
    };

    (function() {
        var script = document.createElement('script');
        script.type = 'text/javascript'; script.async = true;
        script.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'media.reformal.ru/widgets/v3/reformal.js';
        document.getElementsByTagName('head')[0].appendChild(script);
    })();
}
</script><noscript><a href="http://reformal.ru"><img src="http://media.reformal.ru/reformal.png" /></a><a href="http://opencorpora.reformal.ru">Oтзывы и предложения для opencorpora</a></noscript>
{block name=javascripts}{/block}
</body>
</html>
