{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
<h3><a href='sentence.php?id={$diff.sent_id}'>Sentence {$diff.sent_id}</a>, changed by {$diff.user_name|default:'Robot'} on {$diff.timestamp|date_format:"%d.%m.%Y at %H:%M"}</h3>
<p>
{if $diff.comment}
<b>Comment:</b> {$diff.comment}
{else}
No comment.
{/if}
</p>
{if $is_logged && $diff.prev_set && !$diff.next_set}
<p><a class="hint" href="#" onclick="show(byid('revert_form')); return false">Revert the whole changeset</a></p>
<form id="revert_form" action="{$web_prefix}/revert.php?set_id={$diff.set_id}" method="post" style="display:none">
    Comment: <input name='comment' value='Отмена правки {$diff.user_name}, возврат к предыдущей версии' size='60'/>&nbsp;
    <input type='button' onclick="submit_with_readonly_check(document.forms[0])" value="Revert"/>
</form>
{/if}
<table border='1' cellspacing='0' cellpadding='3'>
    <tr>
        <td>{if $diff.prev_set}<a href='?sent_id={$diff.sent_id}&amp;set_id={$diff.prev_set}'>&lt; previous version</a>{else}&nbsp;{/if}</td>
        <td align='right'>{if $diff.next_set}<a href='?sent_id={$diff.sent_id}&amp;set_id={$diff.next_set}'>next version &gt;</a>{else}&nbsp;{/if}</td>
    </tr>
{foreach from=$diff.tokens item=token}
    <tr><th colspan='2'>{$token.pos}</tr>
    <tr>
{if $token.old_ver > 0}
        <td valign='top'><b>(Before the edit)<br/>Revision {$token.old_ver} ({$token.old_user_name|default:'Robot'}, {$token.old_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.old_rev_xml|format_xml|htmlspecialchars}</pre></td>
        <td valign='top'><b>(After the edit)<br/>Revision {$token.new_ver} ({$token.new_user_name|default:'Robot'}, {$token.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{else}
        <td valign='top'><b>New sentence</b></td>
        <td valign='top'><b>Revision {$token.new_ver} ({$token.new_user_name|default:'Robot'}, {$token.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{/if}
    </tr>
{/foreach}
</table>
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
