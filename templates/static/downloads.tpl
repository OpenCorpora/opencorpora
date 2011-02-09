{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>{t}Материалы для скачивания{/t}</h1>
<h2>{t}Морфологический словарь{/t}</h2>
<p><a href="{$web_prefix}/files/export/dict/dict.opcorpora.xml.bz2">{t}Словарь{/t}</a> (xml, {t}обновлён{/t} {$dl.dict.updated}, {$dl.dict.size} {t}Мб{/t})</p>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
