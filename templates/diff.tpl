{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h3><a href='sentence.php?id={$diff.sent_id}'>Предложение {$diff.sent_id}</a>, изменил {$diff.user_name|default:'Робот'} {$diff.timestamp|date_format:"%d.%m.%Y, %H:%M"}</h3>
<p>
{if $diff.comment}
<b>Комментарий:</b> {$diff.comment}
{else}
Без комментария.
{/if}
</p>
{if $is_logged && $diff.prev_set && !$diff.next_set}
<p><a class="hint" href="#" onclick="$('#revert_form').show(); return false">Вернуть предыдущую редакцию предложения</a></p>
<form id="revert_form" action="{$web_prefix}/revert.php?set_id={$diff.set_id}" method="post" style="display:none">
    Комментарий: <input name='comment' value='Отмена правки {$diff.user_name}, возврат к предыдущей версии' size='60'/>&nbsp;
    <input type='button' onclick="submit_with_readonly_check($(this).closest('form'))" value="Вернуть"/>
</form>
{/if}
<table class='table borderless'>
    <tr>
        <td>{if $diff.prev_set}<a href='?sent_id={$diff.sent_id}&amp;set_id={$diff.prev_set}'>&lt; предыдущая версия</a>{else}&nbsp;{/if}</td>
        <td align='right'>{if $diff.next_set}<a href='?sent_id={$diff.sent_id}&amp;set_id={$diff.next_set}'>следующая версия &gt;</a>{else}&nbsp;{/if}</td>
    </tr>
{foreach from=$diff.tokens item=token}
    <tr><th colspan='2'>{$token.pos}</tr>
    <tr>
{if $token.old_ver > 0}
        <td valign='top'><b>(Было)</b>
        {if $is_logged}
        <form class='inline' id='form_revert_t{$token.old_ver}' method='post' action='{$web_prefix}/revert.php?tf_rev={$token.old_ver}'><button type="button" onclick="submit_with_readonly_check($('#form_revert_t{$token.old_ver}'))">Вернуть эту версию</button></form>
        {/if}
        <br/><b>Версия {$token.old_ver} ({$token.old_user_name|default:'Робот'}, {$token.old_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b></td>
        <td valign='top'><b>(Стало)<br/>Версия {$token.new_ver} ({$token.new_user_name|default:'Робот'}, {$token.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b></td>
{else}
        <td valign='top'><b>Новое предложение</b></td>
        <td valign='top'><b>Версия {$token.new_ver} ({$token.new_user_name|default:'Робот'}, {$token.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.new_rev_xml|htmlspecialchars}</pre></td>
{/if}
    </tr>
    <tr><td><pre>
{foreach from=$token.diff[0] item=str}
<span class="{if $str[1] == 1}bgpink{elseif $str[1] == 2}bggreen{elseif $str[1] == 3}bgyellow{/if}">{$str[2]|htmlspecialchars}</span>
{/foreach}</pre></td><td><pre>
{foreach from=$token.diff[1] item=str}
<span class="{if $str[1] == 1}bgpink{elseif $str[1] == 2}bggreen{elseif $str[1] == 3}bgyellow{/if}">{$str[2]|htmlspecialchars}</span>
{/foreach}</pre></td>
    </tr>
{/foreach}
</table>
{/block}
