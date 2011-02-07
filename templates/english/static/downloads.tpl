{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
<h1>Downloads</h1>
<h2>Morphological dictionary</h2>
<p><a href="{$web_prefix}/files/export/dict/dict.opcorpora.xml.bz2">Dictionary</a> (xml, updated {$dl.dict.updated}, {$dl.dict.size} Мб)</p>
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
