<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv='content' content='text/html;charset=utf-8'/>
        <title>OpenCorpora: открытый корпус русского языка</title>
        
        <!-- Bootstrap -->
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css?4'/>
        <link rel='stylesheet' type='text/css' href='http://yandex.st/jquery-ui/1.8.16/themes/smoothness/jquery.ui.all.min.css'/>
        <script src="{$web_prefix}/js/jquery-1.8.1.min.js"></script>
        <script type="text/javascript" src="http://yandex.st/jquery-ui/1.8.16/jquery-ui.min.js"></script>
        <script src='{$web_prefix}/js/main.js?3' type='text/javascript'></script>
        <script src='{$web_prefix}/js/jquery.mousewheel.js' type='text/javascript'></script>
        <script src='{$web_prefix}/js/jquery.autocomplete.js'></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        
        <!-- VK api -->
        <script type="text/javascript" src="//vk.com/js/api/openapi.js?59"></script>
    </head>
{block name=body}<body>{/block}
<div id="fb-root"></div>
<script>
// FB init
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1&appId=459706510739356";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

// VK init
VK.init({ apiId: 3183828, onlyWidgets: true });
</script>
<div id='wrap'>
{nocache}{include file='header.tpl'}
{block name=before_content}{/block}{/nocache}
{if $readonly == 1}
<div class='alert alert-error'><div class="container">Система находится в режиме &laquo;только для чтения&raquo;. Функции разметки и редактирования временно не работают.</div></div>
{/if}
<div id="container" class="container">
{if $game_is_on == 1}{include file='qa/user_splash.tpl'}{/if}
<div id="alert_wrap">{if $alerts}{foreach $alerts as $type=>$message}<div class="alert alert-{$type}">{$message}</div>{/foreach}<script>setTimeout('$("#alert_wrap .alert").fadeOut()',3000);</script>{/if}
</div>
{block name=content}{/block}
</div>
{include file='footer.tpl'}
</div>
</body>
</html>
