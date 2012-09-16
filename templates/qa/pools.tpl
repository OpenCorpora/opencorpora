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
<p>Пул добавлен. Когда примеры найдутся, пул появится на <a href="?type=1">этой странице</a>.</p>
{/if}

{* Type chooser *}
<ul class="nav nav-tabs">
<li{if $smarty.get.type == 0} class="active"{/if}><a href="?type=0">идёт поиск примеров</a></li>
<li{if $smarty.get.type == 1} class="active"{/if}><a href="?type=1">найдены примеры</a></li>
<li{if $smarty.get.type == 2} class="active"{/if}><a href="?type=2">не опубликованные</a></li>
<li{if $smarty.get.type == 3} class="active"{/if}><a href="?type=3">опубликованные</a></li>
<li{if $smarty.get.type == 4} class="active"{/if}><a href="?type=4">снятые с публикации</a></li>
<li{if $smarty.get.type == 5} class="active"{/if}><a href="?type=5">на модерации</a></li>
<li{if $smarty.get.type == 6} class="active"{/if}><a href="?type=6">модерация окончена</a></li>
</ul>
<table class="table">
<tr class="borderless">
    <th>ID</th>
    <th>Имя</th>
    <th>Условия</th>
    <th>Обновлён</th>
    <th>Автор</th>
    {if $smarty.get.type > 4}<th>Модератор</th>{/if}
    {if $smarty.get.type > 0}<th>Состояние</th>{/if}
</tr>
{foreach from=$pools item=pool}
<tr>
    <td>{$pool.pool_id}</td>
    <td>{strip}
        {if $pool.status > 0}<a href="?act={if $pool.status == 1}candidates{else}samples{/if}&amp;pool_id={$pool.pool_id}">{/if}
        {$pool.pool_name|htmlspecialchars}
        {if $pool.status > 0}</a>{/if}
        {if $pool.comment}<br/><span class='small'>{$pool.comment|htmlspecialchars}</span>{/if}
        {if $user_permission_check_morph}<br/><a href="?act=delete&amp;pool_id={$pool.pool_id}" class='del'>удалить</a>{/if}
    {/strip}</td>
    <td>{$pool.grammemes|htmlspecialchars}<br/><span class='small'>{$pool.gram_descr|htmlspecialchars}</span><br/>Оценок: {$pool.users_needed}<br/>Токенизация: {$pool.token_check}</td>
    <td>{$pool.updated_ts|date_format:"%a %d.%m.%Y, %H:%M"}</td>
    <td>{$pool.author_name|htmlspecialchars}</td>
    {if $pool.status > 4}
        <td>{$pool.moderator_name|default:"&ndash;"}</td>
    {/if}
    {if $pool.status == 1}
        <td>найдено примеров: {$pool.candidate_count}</td>
    {elseif $pool.status == 5}
        <td>проверено: {$pool.moderated_count}/{$pool.instance_count / $pool.users_needed}</td>
    {elseif $pool.status > 1}
        <td><span class='{if $pool.instance_count > 0 && $pool.answer_count == $pool.instance_count}bggreen{/if}'>{$pool.answer_count}/{$pool.instance_count} ответов</span></td>
    {/if}
</tr>
{foreachelse}
<tr><td colspan='7'>Нет ни одного пула.</tr>
{/foreach}
</table><br/>
{if $user_permission_check_morph}
<a href="#" class="pseudo" id="add_pool">Добавить новый пул</a>
<form id="f_add" style="display:none" method="post" action="?act=add"><table border="0" cellspacing="5">
<tr><td>Название:<td><input name="pool_name" maxlength="120" size="60" type="text" placeholder="Название пула"/></tr>
<tr>
    <td>Граммемы:<br/><span class='small'>лишние оставить пустыми</span>
    <td>
        <input name="gram[]" placeholder="gram1&gram2" size='16' type="text">
        <input name="gram[]" placeholder="gram3|gram4" size='16' type="text"/>
        <input name="gram[]" placeholder="gram5&gram6&gram7" size='16' type="text"/>
        <input name="gram[]" placeholder="gram8&gram9&gram10" size='16' type="text"/>
        <input name="gram[]" placeholder="gram8|gram9|gram10" size='16' type="text"/>
</tr>
<tr>
    <td>Описания к ним:<br/><span class='small'>их увидят разметчики</span>
    <td>
        <input name="descr[]" placeholder="глагол" maxlength='127' size='16' type="text"/>
        <input name="descr[]" placeholder="прилагательное" maxlength='127' type="text" size='16'/>
        <input name="descr[]" placeholder="наречие" maxlength='127' size='16' type="text"/>
        <input name="descr[]" placeholder="предлог" maxlength='127' size='16' type="text"/>
        <input name="descr[]" placeholder="42" maxlength='127' size='16' type="text"/>
</tr>
<tr><td valign="top">Комментарий:<td><textarea name="comment" cols="40" rows="4" type="text"></textarea></tr>
<tr><td>Желаемое число оценок<td><input name="users_needed" maxlength="2" size="3" value="5" type="text"/>
<tr><td>Брать только примеры с<td><input name="token_checked" size="3" maxlength="2" value="0" type="text"/> и более подтверждениями токенизации </tr>
<tr><td colspan="2"><button class="btn btn-large">Начать поиск примеров</button></tr>
</table></form>
{/if}
{/block}
