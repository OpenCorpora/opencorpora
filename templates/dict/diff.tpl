{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h3><a href='dict.php?act=edit&amp;id={$diff.lemma_id}'>{t}Лемма{/t} {$diff.lemma_id}</a>, {t}изменил{/t} {$diff.new_user_name|default:'Робот'} {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"}</h3>
<p>
{if $diff.comment}
<b>{t}Комментарий{/t}:</b> {$diff.comment}
{else}
{t}Без комментария.{/t}
{/if}
</p>
<table border='1' cellspacing='0' cellpadding='3'>
    <tr>
        <td>{if $diff.prev_set}<a href='?lemma_id={$diff.lemma_id}&amp;set_id={$diff.prev_set}'>&lt; {t}предыдущая версия{/t}</a>{else}&nbsp;{/if}</td>
        <td align='right'>{if $diff.next_set}<a href='?lemma_id={$diff.lemma_id}&amp;set_id={$diff.next_set}'>{t}следующая версия{/t} &gt;</a>{else}&nbsp;{/if}</td>
    </tr>
    <tr>
{if $diff.old_ver > 0}
        <td valign='top'><b>({t}Было{/t})</b>
        {if $is_logged}
        <form class='inline' id='form_revert_t{$diff.old_ver}' method='post' action='{$web_prefix}/revert.php?dict_rev={$diff.old_ver}'><button onclick="submit_with_readonly_check(byid('form_revert_t{$diff.old_ver}'))">{t}Вернуть эту версию{/t}</button></form>
        {/if}
        <br/><b>{t}Версия{/t} {$diff.old_ver} ({$diff.old_user_name|default:'Робот'}, {$diff.old_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.old_rev_xml|format_xml|htmlspecialchars}</pre></td>
        <td valign='top'><b>({t}Стало{/t})<br/>{t}Версия{/t} {$diff.new_ver} ({$diff.new_user_name|default:'Робот'}, {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{else}
        <td valign='top'><b>{t}Новая лемма{/t}</b></td>
        <td valign='top'><b>{t}Версия{/t} {$diff.new_ver} ({$diff.new_user_name|default:'Робот'}, {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{/if}
    </tr>
</table>
{/block}
