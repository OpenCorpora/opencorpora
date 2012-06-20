{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Доступные задания</h1>
<table border="1" cellspacing="0" cellpadding="3">
<tr><th>Название пула</th><th>Сделано мной</th><th>Доступно</th>{if $available}<th>&nbsp;</th>{/if}</tr>
{foreach from=$available item=task}
<tr>
    <td>{$task.name|htmlspecialchars}</td>
    <td>{if $task.num_done > 0}<a href="?act=my&amp;pool_id={$task.id}">{$task.num_done}</a>{else}0{/if}</td>
    <td>{$task.num}{if $task.num_started} +{$task.num_started} начатых{/if}</td>
    <td>
        {if $task.status == 3}
            {if $task.num || $task.num_started}<a href="?act=annot&amp;pool_id={$task.id}">взять на разметку</a>{else}&nbsp;{/if}
        {else}
            только чтение
        {/if}
    </td>
</tr>
{foreachelse}
<tr><td colspan='3'>Нет доступных заданий.</td></tr>
{/foreach}
</table>
{/block}
