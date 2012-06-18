{include file='commonhtmlheader.tpl'}
{block name=body}<body>{/block}
<div id='main'>
{nocache}{include file='header.tpl'}{/nocache}
<div id='content'{if $smarty.session.hidemenu} class="content-wide"{/if}>
{block name=content}{/block}
</div>
<div id='rightcol'{if $smarty.session.hidemenu} class="rightcol-narrow"{/if}>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
