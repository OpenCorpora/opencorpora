{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<table border='1' cellspacing='0' cellpadding='3'>
{foreach item=comment from=$comments}
    <tr>
        <td><a href="{$web_prefix}/sentence.php?id={$comment.sent_id}">Предложение {$comment.sent_id}</a></td>
        <td>{$comment.user_name|htmlspecialchars}</td>
        <td>{$comment.ts|date_format:"%a %d.%m.%Y, %H:%M"}</td>
        <td><a href="{$web_prefix}/sentence.php?id={$comment.sent_id}#comm_{$comment.id}">{$comment.text|htmlspecialchars}</a></td>
    </tr>
{/foreach}
</table>
{/block}
