{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Отмодерированные примеры, которые не изменили корпус</h1>
Общая статистика:
<ul>
<li>Всего: {count($data.samples)}
<li>Опечатка: {$data.total[$smarty.const.MA_SAMPLES_STATUS_MISPRINT]}</li>
<li>Неснимаемая омонимия: {$data.total[$smarty.const.MA_SAMPLES_STATUS_HOMONYMOUS]}</li>
<li>Ручная правка: {$data.total[$smarty.const.MA_SAMPLES_STATUS_MANUAL_EDIT]}</li>
<li>???: {$data.total[-1]|default:0}</li>
</ul>
<table class='table'>
{foreach from=$data.samples item=sample}
<tr>
    <td>{$sample.id}</td>
    <td>{$sample.pool_name}</td>
    <td>{strip}
        {if     $sample.mod_status == $smarty.const.MA_SAMPLES_STATUS_MISPRINT}опечатка
        {elseif $sample.mod_status == $smarty.const.MA_SAMPLES_STATUS_HOMONYMOUS}неснимаемая омонимия
        {elseif $sample.mod_status == $smarty.const.MA_SAMPLES_STATUS_MANUAL_EDIT}<a href="{$web_prefix}/diff.php?rev_id={$sample.revision}">ручная правка</a>
        {else}???
        {/if}
        {/strip}
    </td>
</tr>
{/foreach}
</table>
{/block}
