{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Отмодерированные примеры, которые не изменили корпус</h1>
Общая статистика:
<ul>
<li>Всего: {count($data.samples)}
<li>Опечатка: {$data.total[3]}</li>
<li>Неснимаемая омонимия: {$data.total[4]}</li>
<li>Ручная правка: {$data.total[5]}</li>
<li>???: {$data.total[-1]|default:0}</li>
</ul>
<table class='table'>
{foreach from=$data.samples item=sample}
<tr>
    <td>{$sample.id}</td>
    <td>{$sample.pool_name}</td>
    <td>{$sample.pool_revision}</td>
    <td>{strip}
        {if     $sample.mod_status == 3}опечатка
        {elseif $sample.mod_status == 4}неснимаемая омонимия
        {elseif $sample.mod_status == 5}ручная правка
        {else}???
        {/if}
        {/strip}
    </td>
</tr>
{/foreach}
</table>
{/block}
