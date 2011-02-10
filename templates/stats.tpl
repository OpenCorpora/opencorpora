{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>{t}Статистика{/t}</h1>
{t}Пользователей{/t}: {$stats.cnt_users}<br/>
<a href="{$web_prefix}/books.php">{t}Книг{/t}</a>:</b> {$stats.cnt_books}<br/>
{t}Предложений{/t}: {$stats.cnt_sent}<br/>
{t}Словоупотреблений{/t}: {$stats.cnt_words}<br/>
<h2>{t}Словарь{/t}</h2>
{t}Лемм{/t}: {$stats.cnt_lemmata}<br/>
{t}Форм{/t}: {$stats.cnt_forms}
</div>`
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
