{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
<h1>Stats</h1>
Users: {$stats.cnt_users}<br/>
<a href="{$web_prefix}/books.php">Books</a>:</b> {$stats.cnt_books}<br/>
Sentences: {$stats.cnt_sent}<br/>
Words: {$stats.cnt_words}<br/>
<h2>Dictionary</h2>
Lemmata: {$stats.cnt_lemmata}<br/>
Word forms: {$stats.cnt_forms}
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
