{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>Свежие правки в словаре</h1>
{if !$smarty.get.lemma_id}
    <p>{t}Всего{/t}: {$history.total}</p>
    <ul class="pager">
        {if $history.total <= ($smarty.get.skip + 20)}<li class="next disabled"><a href="#">{t}раньше{/t} &rarr;</a></li>{else}<li class="next"><a href='?skip={$smarty.get.skip + 20}'>{t}раньше{/t} &rarr;</a></li>{/if}
        {if $smarty.get.skip == 0}<li class="previous disabled"><a href='#'>&larr; {t}позже{/t}</a></li>{else}<li class="previous"><a href='?skip={$smarty.get.skip - 20}'>&larr; {t}позже{/t}</a></li>{/if}
    </ul>
{/if}
<table class="table">
{foreach from=$history.sets item=h}
<tr{if $h.is_link} style='background:yellow'{/if}>
	<td>{$h.set_id}</td>
	<td>{$h.user_name|default:'Робот'}</td>
	<td>{$h.timestamp|date_format:"%a %d.%m.%Y, %H:%M"}</td>
	<td>
        {if $h.is_link && $smarty.get.lemma_id && $smarty.get.lemma_id != $h.lemma_id}
        <a href="dict.php?act=edit&amp;id={$h.lemma2_id}">{$h.lemma2_text}</a>
        {else}
        <a href="dict.php?act=edit&amp;id={$h.lemma_id}">{$h.lemma_text}</a>
        {/if}
    </td>
	<td>
        {if $h.is_link}
        {t}Изменились связи{/t}
        {else}
        <a href="dict_diff.php?lemma_id={$h.lemma_id}&amp;set_id={$h.set_id}">{t}Изменения{/t}</a>
        {/if}
    </td>
    <td>{if $h.comment}{$h.comment}{else}({t}без комментария{/t}){/if}</td>
</tr>
{/foreach}
</table>
{if !$smarty.get.lemma_id}
    <ul class="pager">
        {if $history.total <= ($smarty.get.skip + 20)}<li class="next disabled"><a href="#">{t}раньше{/t} &rarr;</a></li>{else}<li class="next"><a href='?skip={$smarty.get.skip + 20}'>{t}раньше{/t} &rarr;</a></li>{/if}
        {if $smarty.get.skip == 0}<li class="previous disabled"><a href='#'>&larr; {t}позже{/t}</li>{else}<li class="previous"><a href='?skip={$smarty.get.skip - 20}'>&larr; {t}позже{/t}</a></li>{/if}
    </ul>
{/if}
{/block}
