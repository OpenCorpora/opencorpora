{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Пулы, сильнее всего влияющие на объём корпуса со снятой омонимией</h1>
<p>Список хороших предложений обновляется раз в сутки.</p>
<table class="table">
<tr><td>Пул<td>Хороших (1, 2) предложений<td>Циферка</tr>
{foreach item=pool from=$pools}
<tr><td><a href="pools.php?act=samples&amp;pool_id={$pool.id}">{$pool.name|htmlspecialchars}</a><td>{$pool.count}<td>{$pool.count2}</tr>
{/foreach}
</table>
{/block}
