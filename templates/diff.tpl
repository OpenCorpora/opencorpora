{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h3><a href='sentence.php?id={$diff.sent_id}'>{t}Предложение{/t} {$diff.sent_id}</a>, {t}изменил{/t} {$diff.user_name|default:'Робот'} {$diff.timestamp|date_format:"%d.%m.%Y, %H:%M"}</h3>
<p>
{if $diff.comment}
<b>{t}Комментарий{/t}:</b> {$diff.comment}
{else}
{t}Без комментария.{/t}
{/if}
</p>
{if $is_logged && $diff.prev_set && !$diff.next_set}
<p><a class="hint" href="#" onclick="$('#revert_form').show(); return false">{t}Вернуть предыдущую редакцию предложения{/t}</a></p>
<form id="revert_form" action="{$web_prefix}/revert.php?set_id={$diff.set_id}" method="post" style="display:none">
    {t}Комментарий{/t}: <input name='comment' value='Отмена правки {$diff.user_name}, возврат к предыдущей версии' size='60'/>&nbsp;
    <input type='button' onclick="submit_with_readonly_check(document.forms[0])" value="{t}Вернуть{/t}"/>
</form>
{/if}
<table border='1' cellspacing='0' cellpadding='3'>
    <tr>
        <td>{if $diff.prev_set}<a href='?sent_id={$diff.sent_id}&amp;set_id={$diff.prev_set}'>&lt; {t}предыдущая версия{/t}</a>{else}&nbsp;{/if}</td>
        <td align='right'>{if $diff.next_set}<a href='?sent_id={$diff.sent_id}&amp;set_id={$diff.next_set}'>{t}следующая версия{/t} &gt;</a>{else}&nbsp;{/if}</td>
    </tr>
{foreach from=$diff.tokens item=token}
    <tr><th colspan='2'>{$token.pos}</tr>
    <tr>
{if $token.old_ver > 0}
        <td valign='top'><b>({t}Было{/t})</b>
        {if $is_logged}
        <form class='inline' id='form_revert_t{$token.old_ver}' method='post' action='{$web_prefix}/revert.php?tf_rev={$token.old_ver}'><button onclick="submit_with_readonly_check(byid('form_revert_t{$token.old_ver}'))">{t}Вернуть эту версию{/t}</button></form>
        {/if}
        <br/><b>{t}Версия{/t} {$token.old_ver} ({$token.old_user_name|default:'Робот'}, {$token.old_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.old_rev_xml|format_xml|htmlspecialchars}</pre></td>
        <td valign='top'><b>({t}Стало{/t})<br/>{t}Версия{/t} {$token.new_ver} ({$token.new_user_name|default:'Робот'}, {$token.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{else}
        <td valign='top'><b>{t}Новое предложение{/t}</b></td>
        <td valign='top'><b>{t}Версия{/t} {$token.new_ver} ({$token.new_user_name|default:'Робот'}, {$token.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$token.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{/if}
    </tr>
{/foreach}
</table>
{/block}
