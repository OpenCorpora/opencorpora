{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>Открытый корпус</h1>
<p>Здравствуйте!</p>
<p>Это сайт проекта &laquo;Открытый корпус&raquo; (OpenCorpora). Наша цель &ndash; создать морфологически, синтаксически и семантически размеченный корпус текстов на русском языке, в полном объёме доступный для исследователей и редактируемый пользователями.</p>
<p>Мы начали работу в 2009 году, сейчас идёт разработка. Следить за тем, как мы продвигаемся, можно <a href="http://opencorpora.googlecode.com">здесь</a> (да, код проекта открыт).</p>
<h2>Как я могу помочь?</h2>
<p>Если вы:</p>
<ul>
<li>интересуетесь компьютерной лингвистикой и хотите поучаствовать в настоящем проекте;</li>
<li>хотя бы немного умеете программировать;</li>
<li>не знаете ничего о лингвистике и программировании, но вам просто интересно</li>
</ul>
<p>&ndash; пишите нам на <b>opencorpora [at] opencorpora.org</b></p>
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
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
