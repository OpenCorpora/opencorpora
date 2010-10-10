{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h3><a href='sentence.php?id={$diff.sent_id}'>Предложение {$diff.sent_id}</a>, изменил {$diff.user_name|default:'Робот'} {$diff.timestamp|date_format:"%d.%m.%Y в %H:%M"}</h3>
<table border='1' cellspacing='0' cellpadding='3'>
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
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
