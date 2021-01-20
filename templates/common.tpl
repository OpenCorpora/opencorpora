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
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,800,700&subset=latin,cyrillic" rel="stylesheet" type="text/css">

        {block name=styles}{/block}

        <script src="/assets/vendor/jquery/jquery.min.js"></script>
        <script src="/assets/js/main.js?3" type="text/javascript"></script>
        <script src="/assets/js/jquery.mousewheel.js" type="text/javascript"></script>
        <script src="/assets/vendor/bootstrap/js/bootstrap.min.js"></script>
        <script src="/assets/vendor/bootstrap-notify/js/bootstrap-notify.js?1"></script>

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
{block name=javascripts}{/block}
</body>
</html>
