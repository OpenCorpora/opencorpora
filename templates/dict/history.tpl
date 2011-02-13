{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<table border='1' cellspacing='0' cellpadding='3'>
{foreach from=$history item=h}
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
{/block}
