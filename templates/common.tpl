{include file='commonhtmlheader.tpl'}
{block name=body}<body>{/block}
<div id='main'>
{nocache}{include file='header.tpl'}{/nocache}
<div id='rightcol'{if $smarty.session.hidemenu} class="rightcol-narrow"{/if}>
{include file='right.tpl'}
</div>
<div id='content'{if $smarty.session.hidemenu} class="content-wide"{/if}>
{block name=content}{/block}
</div>
<div style="clear:both;"></div>
{include file='footer.tpl'}
</div>
</body>
{include file='commonhtmlfooter.tpl'}
