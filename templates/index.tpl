{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>{t}Открытый корпус{/t}</h1>
<p>{t}Здравствуйте!{/t}</p>
<p>{t}Это сайт проекта &laquo;Открытый корпус&raquo; (OpenCorpora). Наша цель &ndash; создать морфологически, синтаксически и семантически размеченный корпус текстов на русском языке, в полном объёме доступный для исследователей и редактируемый пользователями.{/t}</p>
<p>{t}Мы начали работу в 2009 году, сейчас идёт разработка. Следить за тем, как мы продвигаемся, можно{/t} <a href="http://opencorpora.googlecode.com">{t}здесь{/t}</a> ({t}да, код проекта открыт{/t}).</p>
<h2>{t}Как я могу помочь?{/t}</h2>
<p>{t}Если вы:{/t}</p>
<ul>
<li>{t}интересуетесь компьютерной лингвистикой и хотите поучаствовать в настоящем проекте;{/t}</li>
<li>{t}хотя бы немного умеете программировать;{/t}</li>
<li>{t}не знаете ничего о лингвистике и программировании, но вам просто интересно{/t}</li>
</ul>
<p>&ndash; {t}пишите нам на{/t} <b>{mailto address=opencorpora@opencorpora.org encode=javascript}</b></p>
{* Admin options *}
{if $is_admin == 1}
    <a href='{$web_prefix}/books.php'>{t}Редактор источников{/t}</a><br/>
    <a href='{$web_prefix}/dict.php'>{t}Редактор словаря{/t}</a><br/><br/>
    <a href='{$web_prefix}/add.php'>{t}Добавить текст{/t}</a><br/>
    <br/>
{/if}
<a href='?rand'>{t}Случайное предложение{/t}</a><br/>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
