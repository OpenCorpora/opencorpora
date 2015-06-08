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
    $('select.pool_select').change(function(event){
        if ($(event.target).val() > 0)
            $('tr.ex_pool').hide('slow');
        else
            $('tr.ex_pool').show('slow');
    });
    $('#moder_id').val({/literal}{$smarty.get.moder_id}{literal});
    $('#moder_id').change(function() {
        location.href = "?type={/literal}{$smarty.get.type}{literal}&moder_id=" + $(this).val();
    });
    $('#gram-filter input[type=button]').click(function() {
        var reg = $("#gram-cond").val();
        {/literal}
        location.href = "?type={$smarty.get.type}&moder_id={if isset($smarty.get.moder_id)}{$smarty.get.moder_id}{else}0{/if}&filter=" + encodeURIComponent(reg);
        {literal}
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
<li{if $smarty.get.type == $smarty.const.MA_POOLS_STATUS_FOUND_CANDIDATES} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_FOUND_CANDIDATES}">найдены примеры</a></li>
<li{if $smarty.get.type == $smarty.const.MA_POOLS_STATUS_NOT_STARTED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_NOT_STARTED}">не опубликованные</a></li>
<li{if $smarty.get.type == $smarty.const.MA_POOLS_STATUS_IN_PROGRESS} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_IN_PROGRESS}">опубликованные</a></li>
<li{if $smarty.get.type == $smarty.const.MA_POOLS_STATUS_ANSWERED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_ANSWERED}">снятые с публикации</a></li>
<li{if $smarty.get.type == $smarty.const.MA_POOLS_STATUS_MODERATION} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_MODERATION}{if isset($smarty.get.moder_id)}&amp;moder_id={$smarty.get.moder_id}{/if}">на модерации</a></li>
<li{if $smarty.get.type == $smarty.const.MA_POOLS_STATUS_MODERATED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_MODERATED}{if isset($smarty.get.moder_id)}&amp;moder_id={$smarty.get.moder_id}{/if}">модерация окончена</a></li>
<li{if $smarty.get.type == $smarty.const.MA_POOLS_STATUS_TO_MERGE} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_TO_MERGE}{if isset($smarty.get.moder_id)}&amp;moder_id={$smarty.get.moder_id}{/if}">в очереди на переливку</a></li>
<li{if $smarty.get.type == $smarty.const.MA_POOLS_STATUS_ARCHIVED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_ARCHIVED}{if isset($smarty.get.moder_id)}&amp;moder_id={$smarty.get.moder_id}{/if}">в архиве</a></li>
</ul>
<table class="table">
<tr class="borderless">
    <th>ID</th>
    <th>Имя</th>
    <th>Условия<br/><form class='form-inline' id='gram-filter'><input type='text' id='gram-cond' placeholder='фильтр (regexp)' value='{if isset($smarty.get.filter)}{$smarty.get.filter|htmlspecialchars}{/if}' class='span2'/> <input type='button' value='OK' class='btn'/></form></th>
    <th>Обновлён</th>
    <th>Автор</th>
    {if $smarty.get.type > $smarty.const.MA_POOLS_STATUS_ANSWERED}<th>{html_options name=moder_id options=$pools.moderators id=moder_id}</th>{/if}
    {if $smarty.get.type > 0}<th>Состояние</th>{/if}
</tr>
{foreach from=$pools.pools item=pool}
<tr>
    <td>{$pool.pool_id}</td>
    <td>{strip}
        {if $pool.status > 0}<a href="?act={if $pool.status == $smarty.const.MA_POOLS_STATUS_NOT_STARTED}candidates{else}samples{/if}&amp;pool_id={$pool.pool_id}">{/if}
        {$pool.pool_name|htmlspecialchars}
        {if $pool.status > 0}</a>{/if}
        {if $user_permission_check_morph}<br/><a href="?act=delete&amp;pool_id={$pool.pool_id}" class='del'>удалить</a>{/if}
    {/strip}</td>
    <td>{$pool.grammemes|htmlspecialchars}<br/><span class='small'>{$pool.gram_descr|htmlspecialchars}</span><br/>Оценок: {$pool.users_needed}<br/>Токенизация: {$pool.token_check}</td>
    <td>{$pool.updated_ts|date_format:"%a %d.%m.%Y, %H:%M"}</td>
    <td>{$pool.author_name|htmlspecialchars}</td>
    {if $pool.status > $smarty.const.MA_POOLS_STATUS_ANSWERED}
        <td>{$pool.moderator_name|default:"&ndash;"}</td>
    {/if}
    {if $pool.status == $smarty.const.MA_POOLS_STATUS_FOUND_CANDIDATES}
        <td>найдено примеров: {$pool.candidate_count}</td>
    {elseif $pool.status == $smarty.const.MA_POOLS_STATUS_MODERATION}
        <td>проверено: {$pool.moderated_count}/{$pool.instance_count / $pool.users_needed}</td>
    {elseif $pool.status > $smarty.const.MA_POOLS_STATUS_NOT_STARTED}
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
<tr><td>Тип:<td>{html_options name=pool_type options=$pools.types class=pool_select}</tr>
<tr class="ex_pool">
    <td>Граммемы:<br/><span class='small'>лишние оставить пустыми</span>
    <td>
        <input name="gram[]" placeholder="gram1&gram2" type="text" class="span2">
        <input name="gram[]" placeholder="gram3|gram4" type="text" class="span2"/>
        <input name="gram[]" placeholder="gram5&gram6&gram7" type="text" class="span2"/>
        <input name="gram[]" placeholder="gram8&gram9&gram10" type="text" class="span2"/>
        <input name="gram[]" placeholder="gram8|gram9|gram10" type="text" class="span2"/>
        <input name="gram[]" placeholder="gram11" type="text" class="span2"/>
</tr>
<tr class="ex_pool">
    <td>Описания к ним:<br/><span class='small'>их увидят разметчики</span>
    <td>
        <input name="descr[]" placeholder="глагол" maxlength='127' type="text" class="span2"/>
        <input name="descr[]" placeholder="прилагательное" maxlength='127' type="text" class="span2"/>
        <input name="descr[]" placeholder="наречие" maxlength='127' type="text" class="span2"/>
        <input name="descr[]" placeholder="предлог" maxlength='127' type="text" class="span2"/>
        <input name="descr[]" placeholder="42" maxlength='127' type="text" class="span2"/>
        <input name="descr[]" placeholder="двойственное" maxlength='127' type="text" class="span2"/>
</tr>
<tr><td>Желаемое число оценок<td><input name="users_needed" maxlength="2" size="3" value="3" type="text" class="span1"/>
<tr><td>Брать только примеры с<td><input name="token_checked" size="3" maxlength="2" value="0" type="text" class="span1"/> и более подтверждениями токенизации </tr>
<tr><td colspan="2"><button class="btn btn-large btn-primary">Начать поиск примеров</button></tr>
</table></form>
{/if}
{/block}
