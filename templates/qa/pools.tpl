{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function(){
    $('#add_pool').click(function(event){
        $(this).hide();
        $('#f_add').show('slow');
        event.preventDefault();
    });
});
</script>
{/literal}
<h1>Пулы для морфологической разметки</h1>
{if isset($smarty.get.added)}
<p>Пул добавлен. Обновите страницу через несколько минут, чтобы увидеть найденные примеры.</p>
{/if}
<table border="1" cellspacing="0" cellpadding="3">
<tr>
    <th>ID</td>
    <th>Имя</td>
    <th>Условия</td>
    <th>Обновлён</td>
    <th>Автор</td>
    <th>Состояние</td>
</tr>
{foreach from=$pools item=pool}
<tr>
    <td>{$pool.pool_id}</td>
    <td>{$pool.pool_name|htmlspecialchars}{if $pool.comment}<br/><span class='small'>{$pool.comment|htmlspecialchars}</span>{/if}</td>
    <td>{$pool.grammemes|htmlspecialchars}<br/><span class='small'>{$pool.gram_descr|htmlspecialchars}</span><br/>Оценок: {$pool.users_needed}<br/>Токенизация: {$pool.token_check}<br/>Таймаут: {$pool.timeout} c</td>
    <td>{$pool.updated_ts|date_format:"%a %d.%m.%Y, %H:%M"}</td>
    <td>{$pool.user_name|htmlspecialchars}</td>
    <td>{strip}
        {if $pool.status == 0}
            идёт поиск примеров
        {elseif $pool.status == 1}
            <a href="?act=candidates&amp;pool_id={$pool.pool_id}">найдено примеров: {$pool.candidate_count}</a>
        {elseif $pool.status == 2}
            <a href="?act=samples&amp;pool_id={$pool.pool_id}">не опубликован</a>
        {elseif $pool.status == 3}
            <a href="?act=samples&amp;pool_id={$pool.pool_id}">опубликован</a>
        {elseif $pool.status == 4}
            <a href="?act=samples&amp;pool_id={$pool.pool_id}">снят с публикации</a>
        {/if}
    {/strip}</td>
</tr>
{foreachelse}
<tr><td colspan='6'>Нет ни одного пула.</tr>
{/foreach}
</table><br/>
<a href="#" class="hint" id="add_pool">Добавить новый пул</a>
<form id="f_add" style="display:none" method="post" action="?act=add"><table border="0" cellspacing="5">
<tr><td>Название:<td><input name="pool_name" maxlength="120" size="60" placeholder="Название пула"/></tr>
<tr><td>Граммемы:<td><input name="gram1" placeholder="gram1&gram2"/> <input name="gram2" placeholder="gram3|gram4"/></tr>
<tr><td>Описания к ним:<br/><span class='small'>их увидят разметчики</span><td><input name="descr1" placeholder="глагол" maxlength='127'/> <input name="descr2" placeholder="не глагол" maxlength='127'/></tr>
<tr><td valign="top">Комментарий:<td><textarea name="comment" cols="40" rows="4"></textarea></tr>
<tr><td>Желаемое число оценок<td><input name="users_needed" maxlength="2" size="3" value="5"/>
<tr><td>Таймаут по умолчанию<td><input name="timeout" maxlength="5" size="5" value="60"/> секунд на пример</tr>
<tr><td>Брать только примеры с<td><input name="token_checked" size="3" maxlength="2" value="0"/> и более подтверждениями токенизации </tr>
<tr><td colspan="2"><button>Начать поиск примеров</button></tr>
</table></form>
{/block}
