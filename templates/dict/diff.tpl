{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h3><a href='dict.php?act=edit&amp;id={$diff.lemma_id}'>Лемма {$diff.lemma_id}</a>, изменил {$diff.new_user_name|default:'Робот'} {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"}</h3>
<p>
{if $diff.comment}
<b>Комментарий:</b> {$diff.comment}
{else}
Без комментария.
{/if}
</p>
<table class='table borderless'>
    <tr>
        <td>{if $diff.prev_set}<a href='?lemma_id={$diff.lemma_id}&amp;set_id={$diff.prev_set}'>&lt; предыдущая версия</a>{else}&nbsp;{/if}</td>
        <td align='right'>{if $diff.next_set}<a href='?lemma_id={$diff.lemma_id}&amp;set_id={$diff.next_set}'>следующая версия &gt;</a>{else}&nbsp;{/if}</td>
    </tr>
    <tr>
{if $diff.old_ver > 0}
        <td valign='top'><b>(Было)</b>
        {if $is_logged}
        <form class='inline' id='form_revert_t{$diff.old_ver}' method='post' action='{$web_prefix}/revert.php?dict_rev={$diff.old_ver}'><button type="button" onclick="submit_with_readonly_check($('#form_revert_t{$diff.old_ver}'))">Вернуть эту версию</button></form>
        {/if}
        <br/><b>Версия {$diff.old_ver} ({$diff.old_user_name|default:'Робот'}, {$diff.old_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b></td>
        <td valign='top'><b>(Стало)<br/>Версия {$diff.new_ver} ({$diff.new_user_name|default:'Робот'}, {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b></td>
{else}
        <td valign='top'><b>Новая лемма</b></td>
        <td valign='top'><b>Версия {$diff.new_ver} ({$diff.new_user_name|default:'Робот'}, {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.new_rev_xml|htmlspecialchars}</pre></td>
{/if}
    </tr>
    <tr><td><pre>
{foreach from=$diff.diff[0] item=str}
<span class="{if $str[1] == 1}bgpink{elseif $str[1] == 2}bggreen{elseif $str[1] == 3}bgyellow{/if}">{$str[2]|htmlspecialchars}</span>
{/foreach}</pre></td><td><pre>
{foreach from=$diff.diff[1] item=str}
<span class="{if $str[1] == 1}bgpink{elseif $str[1] == 2}bggreen{elseif $str[1] == 3}bgyellow{/if}">{$str[2]|htmlspecialchars}</span>
{/foreach}</pre></td>
    </tr>
</table>
{/block}
