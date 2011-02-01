{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>Материалы для скачивания</h1>
<h2>Морфологический словарь</h2>
<p><a href="{$web_prefix}/files/export/dict/dict.opcorpora.xml.bz2">Словарь</a> (xml, обновлён {$dl.dict.updated}, {$dl.dict.size} Мб)</p>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
