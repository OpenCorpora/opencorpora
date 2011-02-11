{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<table border='1' cellspacing='0' cellpadding='3'>
{foreach from=$history item=h}
<tr>
	<td>{$h.set_id}</td>
	<td>{$h.user_name|default:'Робот'}</td>
	<td>{$h.timestamp|date_format:"%a %d.%m.%Y, %H:%M"}</td>
	<td><a href="sentence.php?id={$h.sent_id}">{t}Предложение{/t} {$h.sent_id}</a></td>
	<td><a href="diff.php?sent_id={$h.sent_id}&amp;set_id={$h.set_id}">{t}Изменения{/t}</a></td>
    <td>{if $h.comment}{$h.comment}{else}({t}без комментария{/t}){/if}</td>
</tr>
{/foreach}
</table>
{/block}
