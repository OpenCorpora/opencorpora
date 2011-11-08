{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Доступные задания</h1>
<table border="1" cellspacing="0" cellpadding="3">
<tr><th>Название пула</th><th>Доступных заданий</th><th>&nbsp;</th></tr>
{foreach from=$available item=task}
<tr><td>{$task.name|htmlspecialchars}</td><td>{$task.num}/{$task.num_started}</td><td><a href="?act=annot&amp;pool_id={$task.id}">взять на разметку</a></td></tr>
{foreachelse}
<tr><td colspan='3'>Нет доступных заданий.</td></tr>
{/foreach}
</table>
{/block}
