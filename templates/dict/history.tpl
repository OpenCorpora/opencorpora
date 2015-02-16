{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>Свежие правки в словаре</h1>
{if !isset($smarty.get.lemma_id)}
    <p>Всего: {$history.total}</p>
    <ul class="pager">
        {if $history.total <= ($skip + 20)}<li class="next disabled"><a href="#">раньше &rarr;</a></li>{else}<li class="next"><a href='?skip={$skip + 20}'>раньше &rarr;</a></li>{/if}
        {if $skip == 0}<li class="previous disabled"><a href='#'>&larr; позже</a></li>{else}<li class="previous"><a href='?skip={$skip - 20}'>&larr; позже</a></li>{/if}
    </ul>
{/if}
<table class="table">
{foreach from=$history.sets item=h}
<tr{if $h.is_link} style='background:yellow'{/if}>
	<td>{$h.set_id}</td>
	<td>{$h.user_name|default:'Робот'}</td>
	<td>{$h.timestamp|date_format:"%a %d.%m.%Y, %H:%M"}</td>
	<td>
        {if $h.is_link && isset($smarty.get.lemma_id) && $smarty.get.lemma_id != $h.lemma_id}
        <a href="dict.php?act=edit&amp;id={$h.lemma2_id}">{$h.lemma2_text}</a>
        {else}
        <a href="dict.php?act=edit&amp;id={$h.lemma_id}" {if $h.is_lemma_deleted}class="bgpink"{/if}>{$h.lemma_text}</a>
        {/if}
    </td>
	<td>
        {if $h.is_link}
        Изменились связи
        {else}
        <a href="dict_diff.php?lemma_id={$h.lemma_id}&amp;set_id={$h.set_id}">Изменения</a>
        {/if}
    </td>
    <td>{if $h.comment}{$h.comment}{else}(без комментария){/if}</td>
</tr>
{/foreach}
</table>
{if !isset($smarty.get.lemma_id)}
    <ul class="pager">
        {if $history.total <= ($skip + 20)}<li class="next disabled"><a href="#">раньше &rarr;</a></li>{else}<li class="next"><a href='?skip={$skip + 20}'>раньше &rarr;</a></li>{/if}
        {if $skip == 0}<li class="previous disabled"><a href='#'>&larr; позже</li>{else}<li class="previous"><a href='?skip={$skip - 20}'>&larr; позже</a></li>{/if}
    </ul>
{/if}
{/block}
