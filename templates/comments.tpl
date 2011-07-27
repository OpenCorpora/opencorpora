{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<table border='1' cellspacing='0' cellpadding='3'>
<tr>
    <td colspan="3">{if $smarty.get.skip > 0}<a href='?skip={$smarty.get.skip - 20}'>&lt; {t}позже{/t}</a>{else}&nbsp;{/if}</td>
    <td align="right">{if $comments.total > ($smarty.get.skip + 20)}<a href='?skip={$smarty.get.skip + 20}'>{t}раньше{/t} &gt;</a>{else}&nbsp;{/if}</td>
</tr>
{foreach item=comment from=$comments.c}
    <tr>
        <td><a href="{$web_prefix}/sentence.php?id={$comment.sent_id}">Предложение {$comment.sent_id}</a></td>
        <td>{$comment.user_name|htmlspecialchars}</td>
        <td>{$comment.ts|date_format:"%a %d.%m.%Y, %H:%M"}</td>
        <td><a href="{$web_prefix}/sentence.php?id={$comment.sent_id}#comm_{$comment.id}">{$comment.text|htmlspecialchars}</a></td>
    </tr>
{/foreach}
<tr>
    <td colspan="3">{if $smarty.get.skip > 0}<a href='?skip={$smarty.get.skip - 20}'>&lt; {t}позже{/t}</a>{else}&nbsp;{/if}</td>
    <td align="right">{if $comments.total > ($smarty.get.skip + 20)}<a href='?skip={$smarty.get.skip + 20}'>{t}раньше{/t} &gt;</a>{else}&nbsp;{/if}</td>
</tr>
</table>
{/block}
