{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
<h3><a href='dict.php?act=edit&amp;id={$diff.lemma_id}'>Лемма {$diff.lemma_id}</a>, изменил {$diff.new_user_name|default:'Робот'} {$diff.new_timestamp|date_format:"%d.%m.%Y в %H:%M"}</h3>
<p>
{if $diff.comment}
<b>Комментарий:</b> {$diff.comment}
{else}
Без комментария.
{/if}
</p>
<table border='1' cellspacing='0' cellpadding='3'>
    <tr>
        <td>{if $diff.prev_set}<a href='?lemma_id={$diff.lemma_id}&amp;set_id={$diff.prev_set}'>&lt; предыдущая версия</a>{else}&nbsp;{/if}</td>
        <td align='right'>{if $diff.next_set}<a href='?lemma_id={$diff.lemma_id}&amp;set_id={$diff.next_set}'>следующая версия &gt;</a>{else}&nbsp;{/if}</td>
    </tr>
    <tr>
{if $diff.old_ver > 0}
        <td valign='top'><b>(Было)<br/>Версия {$diff.old_ver} ({$diff.old_user_name|default:'Робот'}, {$diff.old_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.old_rev_xml|format_xml|htmlspecialchars}</pre></td>
        <td valign='top'><b>(Стало)<br/>Версия {$diff.new_ver} ({$diff.new_user_name|default:'Робот'}, {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{else}
        <td valign='top'><b>Новая лемма</b></td>
        <td valign='top'><b>Версия {$diff.new_ver} ({$diff.new_user_name|default:'Робот'}, {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{/if}
    </tr>
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
