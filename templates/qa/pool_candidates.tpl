{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Найденные примеры для пула</h1>
<p>Показано не более 200 случайно выбранных примеров.</p>
<form method="post" action="?act=promote&amp;type=all&amp;pool_id={$smarty.get.pool_id}"><button>Добавить все примеры в пул</button></form>
или <form action="?act=promote&amp;type=random&amp;pool_id={$smarty.get.pool_id}" class="inline" method="post"><button>Добавить</button> <input name='n' maxlength='4' size='4' value='100'/> случайных примеров</form>
<br/>или <form action="?act=promote&amp;type=first&amp;pool_id={$smarty.get.pool_id}" class="inline" method="post"><button>Добавить</button> <input name='n' maxlength='4' size='4' value='100'/> первых примеров</form>
<br/><br/>
<table border="1" cellspacing='0' cellpadding='2'>
{foreach from=$candidates item=c}
<tr><td>{strip}
    {foreach from=$c.context item=word name=x}
        {if $smarty.foreach.x.index == $c.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>
        {else}{$word|htmlspecialchars}{/if}
        &nbsp;
    {/foreach}
{/strip}</td></tr>
{/foreach}
</table>
{/block}
