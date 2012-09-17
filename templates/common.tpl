{include file='commonhtmlheader.tpl'}
{block name=body}<body>{/block}
<div id='wrap'>
{nocache}{include file='header.tpl'}{/nocache}
{if $readonly == 1}
<div class='alert alert-error'><div class="container">{t}Система находится в режиме &laquo;только для чтения&raquo;{/t}.</div></div>
{/if}
<div id="container" class="container">
<div id="alert_wrap"></div>
{block name=content}{/block}
</div>
{include file='footer.tpl'}
</div>
</body>
{include file='commonhtmlfooter.tpl'}
