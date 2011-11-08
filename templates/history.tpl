{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<table border='1' cellspacing='0' cellpadding='3'>
{if !isset($smarty.get.sent_id)}
<tr>
    <td colspan='{if isset($smarty.get.set_id)}3{else}2{/if}'>{if $skip > 0}<a href='?{if isset($smarty.get.set_id)}set_id={$smarty.get.set_id}&amp;{/if}skip={$skip - 20}&amp;maa={$maa}'>&lt; {t}позже{/t}</a>{else}&nbsp;{/if}</td>
    <td>{t}Всего{/t}: {$history.total}</td>
    <td><a href='?skip={$skip}&amp;maa={1 - $maa}'>показать {if $maa}все{else}только слияния и разбиения{/if}</a></td>
    <td align='right'>{if $history.total > ($skip + 20)}<a href='?{if isset($smarty.get.set_id)}set_id={$smarty.get.set_id}&amp;{/if}skip={$skip + 20}&amp;maa={$maa}'>{t}раньше{/t} &gt;</a>{else}&nbsp;{/if}</td>
</tr>
{/if}
{foreach from=$history.sets item=h}
<tr>
	<td>{$h.set_id}</td>
	<td>{$h.user_name|default:'Робот'}</td>
	<td>{$h.timestamp|date_format:"%a %d.%m.%Y, %H:%M"}</td>
	{if $h.sent_cnt}
        <td><a href="?set_id={$h.set_id}&amp;maa={$maa}">{$h.sent_cnt} предл.</a></td>
    {/if}
    {if $h.sent_id}
        <td><a href="sentence.php?id={$h.sent_id}">{t}Предложение{/t} {$h.sent_id}</a></td>
        <td><a href="diff.php?sent_id={$h.sent_id}&amp;set_id={$h.set_id}">{t}Изменения{/t}</a></td>
    {/if}
    <td>{if $h.comment}{$h.comment|htmlspecialchars}{else}({t}без комментария{/t}){/if}</td>
</tr>
{/foreach}
{if !isset($smarty.get.sent_id)}
<tr>
    <td colspan='{if isset($smarty.get.set_id)}3{else}2{/if}'>{if $skip > 0}<a href='?{if isset($smarty.get.set_id)}set_id={$smarty.get.set_id}&amp;{/if}skip={$skip - 20}&amp;maa={$maa}'>&lt; {t}позже{/t}</a>{else}&nbsp;{/if}</td>
    <td>{t}Всего{/t}: {$history.total}</td>
    <td><a href='?skip={$skip}&amp;maa={1 - $maa}'>показать {if $maa}все{else}только слияния и разбиения{/if}</a></td>
    <td align='right'>{if $history.total > ($skip + 20)}<a href='?{if isset($smarty.get.set_id)}set_id={$smarty.get.set_id}&amp;{/if}skip={$skip + 20}&amp;maa={$maa}'>{t}раньше{/t} &gt;</a>{else}&nbsp;{/if}</td>
</tr>
{/if}
</table>
{/block}
