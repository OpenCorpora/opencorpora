{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Доступные задания</h1>
<table border="1" cellspacing="0" cellpadding="3">
<tr><th>Название пула</th><th>Сделано мной</th><th>Доступно</th>{if $task.num || $task.num_started}<th>&nbsp;</th>{/if}</tr>
{foreach from=$available item=task}
<tr><td>{$task.name|htmlspecialchars}</td><td>{$task.num_done}</td><td>{$task.num}{if $task.num_started} +{$task.num_started} начатых{/if}</td>{if $task.num || $task.num_started}<td><a href="?act=annot&amp;pool_id={$task.id}">взять на разметку</a></td>{/if}</tr>
{foreachelse}
<tr><td colspan='3'>Нет доступных заданий.</td></tr>
{/foreach}
</table>
{/block}
