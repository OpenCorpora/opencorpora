{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
</head>
<body>
{include file='header.tpl'}
<div id='content'>
    <h3>Добавляем текст</h3>
    {if isset($txt)}
        <form action="?act=check" method="post">
            <textarea cols="70" rows="20" name="txt">{$txt}</textarea>
    {else}
        <form action="?act=check" method="post">
            <textarea cols="70" rows="20" name="txt" onClick="this.innerHTML=''; this.onClick=''">Товарищ, помни! Абзацы разделяются двойным переводом строки, предложения &ndash; одинарным; предложение должно быть токенизировано.</textarea>
    {/if}
    <br/><br/>
    <input type="submit" value="Проверить"/>
    </form>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
</body>
</html>
