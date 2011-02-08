{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='russian/header.tpl'}
<div id='content'>
<h3><a href='sentence.php?id={$diff.sent_id}'>Предложение {$diff.sent_id}</a>, изменил {$diff.user_name|default:'Робот'} {$diff.timestamp|date_format:"%d.%m.%Y в %H:%M"}</h3>
<p>
{if $diff.comment}
<b>Комментарий:</b> {$diff.comment}
{else}
Без комментария.
{/if}
</p>
{if $is_logged && $diff.prev_set && !$diff.next_set}
<p><a class="hint" href="#" onclick="show(byid('revert_form')); return false">Вернуть предыдущую редакцию предложения</a></p>
<form id="revert_form" action="{$web_prefix}/revert.php?set_id={$diff.set_id}" method="post" style="display:none">
    Comment: <input name='comment' value='Отмена правки {$diff.user_name}, возврат к предыдущей версии' size='60'/>&nbsp;
    <input type='button' onclick="submit_with_readonly_check(document.forms[0])" value="Вернуть"/>
</form>
{/if}
<table border='1' cellspacing='0' cellpadding='3'>
    <tr>
        <td>{if $diff.prev_set}<a href='?sent_id={$diff.sent_id}&amp;set_id={$diff.prev_set}'>&lt; предыдущая версия</a>{else}&nbsp;{/if}</td>
        <td align='right'>{if $diff.next_set}<a href='?sent_id={$diff.sent_id}&amp;set_id={$diff.next_set}'>следующая версия &gt;</a>{else}&nbsp;{/if}</td>
    </tr>
{foreach from=$diff.tokens item=token}
    <tr><th colspan='2'>{$token.pos}</tr>
    <tr>
{if $token.old_ver > 0}
        <td valign='top'><b>(Было)<br/>Версия {$token.old_ver} ({$token.old_user_name|default:'Робот'}, {$token.old_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.old_rev_xml|format_xml|htmlspecialchars}</pre></td>
        <td valign='top'><b>(Стало)<br/>Версия {$token.new_ver} ({$token.new_user_name|default:'Робот'}, {$token.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{else}
        <td valign='top'><b>Новое предложение</b></td>
        <td valign='top'><b>Версия {$token.new_ver} ({$token.new_user_name|default:'Робот'}, {$token.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{/if}
    </tr>
{/foreach}
</table>
</div>
<div id='rightcol'>
{include file='russian/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
