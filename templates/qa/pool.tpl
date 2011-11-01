{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<p><a href="?">&lt;&lt; к списку пулов</a></p>
<h1>Пул &laquo;{$pool.name}&raquo;</h1>
{if $pool.status == 2}
<form action="?act=publish&amp;pool_id={$pool.id}" method="post">Пул не опубликован. <button>Опубликовать</button></form>
{elseif $pool.status == 3}
<p>Пул опубликован.</p>
<p>{if !isset($smarty.get.ext)}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">к расширенному виду</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}">к обычному виду</a>{/if}</p>
{/if}
<br/>
<table border="1" cellspacing="0" cellpadding="3">
<tr><th>id</th><th>&nbsp;</th><th>Ответов</th></tr>
{foreach from=$pool.samples item=sample}
<tr>
    <td>{$sample.id}</td>
    <td>{foreach from=$sample.context item=word name=x}{if $smarty.foreach.x.index == $sample.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>{else}{$word|htmlspecialchars}{/if} {/foreach}</td>
    <td>{$sample.answered}/{$pool.num_users}</td>
</tr>
{/foreach}
</table>
{/block}
