{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<table border='1' cellspacing='0' cellpadding='3'>
{if !$smarty.get.sent_id}
<tr>
    <td colspan='3'>{if $smarty.get.skip > 0}<a href='?skip={$smarty.get.skip - 20}'>&lt; {t}позже{/t}</a>{else}&nbsp;{/if}</td>
    <td colspan='2'>{t}Всего{/t}: {$history.total}</td>
    <td align='right'>{if $history.total > ($smarty.get.skip + 20)}<a href='?skip={$smarty.get.skip + 20}'>{t}раньше{/t} &gt;</a>{else}&nbsp;{/if}</td>
</tr>
{/if}
{foreach from=$history.sets item=h}
<tr>
	<td>{$h.set_id}</td>
	<td>{$h.user_name|default:'Робот'}</td>
	<td>{$h.timestamp|date_format:"%a %d.%m.%Y, %H:%M"}</td>
	<td><a href="sentence.php?id={$h.sent_id}">{t}Предложение{/t} {$h.sent_id}</a></td>
	<td><a href="diff.php?sent_id={$h.sent_id}&amp;set_id={$h.set_id}">{t}Изменения{/t}</a></td>
    <td>{if $h.comment}{$h.comment|htmlspecialchars}{else}({t}без комментария{/t}){/if}</td>
</tr>
{/foreach}
{if !$smarty.get.sent_id}
<tr>
    <td colspan='3'>{if $smarty.get.skip > 0}<a href='?skip={$smarty.get.skip - 20}'>&lt; {t}позже{/t}</a>{else}&nbsp;{/if}</td>
    <td colspan='2'>{t}Всего{/t}: {$history.total}</td>
    <td align='right'>{if $history.total > ($smarty.get.skip + 20)}<a href='?skip={$smarty.get.skip + 20}'>{t}раньше{/t} &gt;</a>{else}&nbsp;{/if}</td>
</tr>
{/if}
</table>
{/block}
