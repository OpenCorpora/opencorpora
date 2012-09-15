{include file='commonhtmlheader.tpl'}
{block name=body}<body>{/block}
<div id='wrap'>
{nocache}{include file='header.tpl'}{/nocache}
<div id="container" class="container">
{block name=content}{/block}
</div>
{include file='footer.tpl'}
</div>
</body>
{include file='commonhtmlfooter.tpl'}
