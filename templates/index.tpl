{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
</head>
<body>
{include file='header.tpl'}
<div id='content'>
<h1>Открытый корпус</h1>
<p>Здравствуйте!</p>
<p>Это сайт проекта &laquo;Открытый корпус&raquo; (OpenCorpora). Наша цель &ndash; создать корпус текстов на русском языке, в полном объёме доступный для исследователей и редактируемый пользователями.</p>
<p>Мы начали работу в 2009 году, сейчас идёт разработка. Следить за тем, как мы продвигаемся, можно <a href="http://opencorpora.googlecode.com">здесь</a> (да, код проекта открыт).</p>
<h2>Как я могу помочь?</h2>
<p>Если вы:</p>
<ul>
<li>интересуетесь компьютерной лингвистикой и хотите поучаствовать в настоящем проекте;
<li>хотя бы немного умеете программировать;
<li>не знаете ничего о лингвистике и программировании, но вам просто интересно
</ul>
<p>&ndash; пишите нам на <b>opencorpora [at] gmail.com</b></p>
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
