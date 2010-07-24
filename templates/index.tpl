{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
</head>
<body>
{include file='header.tpl'}
<div id='content'>
{* Admin options *}
{if $is_admin == 1}
    <a href='{$web_prefix}/books.php'>Редактор источников</a><br/>
    <a href='{$web_prefix}/dict.php'>Редактор словаря</a><br/><br/>
    <a href='{$web_prefix}/add.php'>Добавить текст</a><br/>
    <br/>
{/if}
<a href='?rand'>Случайное предложение</a><br/>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
</body>
</html>
