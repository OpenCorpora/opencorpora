{* Smarty *}
{* This is the main template inherited by all the others *}
{include file='commonhtmlheader.tpl'}
{block name=body}<body>{/block}
<div id='main'>
{include file='header.tpl'}
<div id='content'>
{block name=content}{/block}
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
