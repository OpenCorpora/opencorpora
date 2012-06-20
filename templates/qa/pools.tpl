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
    $('a.del').click(function(event){
        return confirm('Удалить пул? Пул с ответами не будет удалён.');
    });
});
</script>
{/literal}
<h1>Пулы для морфологической разметки</h1>
{if isset($smarty.get.added)}
<p>Пул добавлен. Обновите страницу через несколько минут, чтобы увидеть найденные примеры.</p>
{/if}

{* Type chooser *}
<div>
{if $smarty.get.type == 0}
<b>идёт поиск примеров</b> |
{else}
<a href="?type=0">идёт поиск примеров</a> |
{/if}
{if $smarty.get.type == 1}
<b>найдены примеры</b> |
{else}
<a href="?type=1">найдены примеры</a> |
{/if}
{if $smarty.get.type == 2}
<b>не опубликованные</b> |
{else}
<a href="?type=2">не опубликованные</a> |
{/if}
{if $smarty.get.type == 3}
<b>опубликованные</b> |
{else}
<a href="?type=3">опубликованные</a> |
{/if}
{if $smarty.get.type == 4}
<b>снятые с публикации</b> |
{else}
<a href="?type=4">снятые с публикации</a> |
{/if}
{if $smarty.get.type == 5}
<b>на модерации</b> |
{else}
<a href="?type=5">на модерации</a> |
{/if}
</div><br/>

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
    <td>
        {$pool.pool_name|htmlspecialchars}{if $pool.comment}<br/><span class='small'>{$pool.comment|htmlspecialchars}</span>{/if}<br/>
        <a href="?act=delete&amp;pool_id={$pool.pool_id}" class='del'>удалить</a>
    </td>
    <td>{$pool.grammemes|htmlspecialchars}<br/><span class='small'>{$pool.gram_descr|htmlspecialchars}</span><br/>Оценок: {$pool.users_needed}<br/>Токенизация: {$pool.token_check}</td>
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
        {elseif $pool.status == 5}
            <a href="?act=samples&amp;pool_id={$pool.pool_id}">на модерации</a>
        {elseif $pool.status == 6}
            <a href="?act=samples&amp;pool_id={$pool.pool_id}">модерация окончена</a>
        {elseif $pool.status == 7}
            <a href="?act=samples&amp;pool_id={$pool.pool_id}">готов (в архиве)</a>
        {/if}
        {if $pool.status > 1}
            <br/><span class='small{if $pool.instance_count > 0 && $pool.answer_count == $pool.instance_count} bggreen{/if}'>Ответов: {$pool.answer_count}/{$pool.instance_count}</span>
        {/if}
    {/strip}</td>
</tr>
{foreachelse}
<tr><td colspan='6'>Нет ни одного пула.</tr>
{/foreach}
</table><br/>
{if $user_permission_check_morph}
<a href="#" class="hint" id="add_pool">Добавить новый пул</a>
<form id="f_add" style="display:none" method="post" action="?act=add"><table border="0" cellspacing="5">
<tr><td>Название:<td><input name="pool_name" maxlength="120" size="60" placeholder="Название пула"/></tr>
<tr>
    <td>Граммемы:<br/><span class='small'>лишние оставить пустыми</span>
    <td>
        <input name="gram[]" placeholder="gram1&gram2" size='16'/>
        <input name="gram[]" placeholder="gram3|gram4" size='16'/>
        <input name="gram[]" placeholder="gram5&gram6&gram7" size='16'/>
        <input name="gram[]" placeholder="gram8&gram9&gram10" size='16'/>
        <input name="gram[]" placeholder="gram8|gram9|gram10" size='16'/>
</tr>
<tr>
    <td>Описания к ним:<br/><span class='small'>их увидят разметчики</span>
    <td>
        <input name="descr[]" placeholder="глагол" maxlength='127' size='16'/>
        <input name="descr[]" placeholder="прилагательное" maxlength='127' size='16'/>
        <input name="descr[]" placeholder="наречие" maxlength='127' size='16'/>
        <input name="descr[]" placeholder="предлог" maxlength='127' size='16'/>
        <input name="descr[]" placeholder="42" maxlength='127' size='16'/>
</tr>
<tr><td valign="top">Комментарий:<td><textarea name="comment" cols="40" rows="4"></textarea></tr>
<tr><td>Желаемое число оценок<td><input name="users_needed" maxlength="2" size="3" value="5"/>
<tr><td>Брать только примеры с<td><input name="token_checked" size="3" maxlength="2" value="0"/> и более подтверждениями токенизации </tr>
<tr><td colspan="2"><button>Начать поиск примеров</button></tr>
</table></form>
{/if}
{/block}
