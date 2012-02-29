{include file='commonhtmlheader.tpl'}
{block name=body}<body>{/block}
<div id='main'>
{nocache}{include file='header.tpl'}{/nocache}
<div id='content'>
{block name=content}{/block}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
