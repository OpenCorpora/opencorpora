{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Токены из словаря, но с разбором UNKN</h1>
<table class="table">
{foreach item=token from=$tokens}
<tr>
    <td {if $token.is_pending}class='bggreen'{/if}><a href="sentence.php?id={$token.sent_id}">{$token.text|htmlspecialchars}</a></td>
    <td>{foreach from=$token.comments item=comment}<p>{$comment.text|htmlspecialchars} <span class='small muted'>({$comment.author|htmlspecialchars})</span></p>{/foreach}</td>
</tr>
{/foreach}
</table>
{/block}
