{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Отмодерированные примеры, которые не изменили корпус</h1>
<table class='table'>
{foreach from=$data item=sample}
<tr>
    <td>{$sample.id}</td>
    <td>{$sample.pool_name}</td>
    <td>{strip}
        {if     $sample.mod_status == 3}опечатка
        {elseif $sample.mod_status == 4}неснимаемая омонимия
        {else}???
        {/if}
        {/strip}
    </td>
</tr>
{/foreach}
</table>
{/block}
