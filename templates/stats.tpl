{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>Статистика</h1>
Пользователей: {$stats.cnt_users}<br/>
<a href="{$web_prefix}/books.php">Книг</a>:</b> {$stats.cnt_books}<br/>
Предложений: {$stats.cnt_sent}<br/>
Словоупотреблений: {$stats.cnt_words}<br/>
<h2>Словарь</h2>
Лемм: {$stats.cnt_lemmata}<br/>
Форм: {$stats.cnt_forms}
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
