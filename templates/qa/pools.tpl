{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function(){
    $('a.del').click(function(event){
        return confirm('Удалить пул? Пул с ответами не будет удалён.');
    });
    $('select.pool_select').change(function(event){
        if ($(event.target).val() > 0)
            $('tr.ex_pool').hide('slow');
        else
            $('tr.ex_pool').show('slow');
    });
    $('#moder_id').val({/literal}{$moder_id}{literal});
    $('#moder_id').change(function() {
        location.href = "?type={/literal}{$type}{literal}&moder_id=" + $(this).val();
    });
    $('#gram-filter input[type=button]').click(function() {
        var reg = $("#gram-cond").val();
        {/literal}
        location.href = "?type={$type}&moder_id={$moder_id}&filter=" + encodeURIComponent(reg);
        {literal}
    });
});
</script>
{/literal}
<h1>Пулы для морфологической разметки</h1>

{* Type chooser *}
<ul class="nav nav-tabs">
<li{if $type == $smarty.const.MA_POOLS_STATUS_FOUND_CANDIDATES} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_FOUND_CANDIDATES}">поиск примеров</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_NOT_STARTED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_NOT_STARTED}">не опубликованные</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_IN_PROGRESS} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_IN_PROGRESS}">опубликованные</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_ANSWERED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_ANSWERED}">снятые с публикации</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_MODERATION} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_MODERATION}&amp;moder_id={$moder_id}">на модерации</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_MODERATED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_MODERATED}&amp;moder_id={$moder_id}">модерация окончена</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_TO_MERGE} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_TO_MERGE}&amp;moder_id={$moder_id}">в очереди на переливку</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_ARCHIVED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_ARCHIVED}&amp;moder_id={$moder_id}">в архиве</a></li>
</ul>
<table class="table">
<tr class="borderless">
    <th>ID</th>
    <th>Имя</th>
    <th>Условия<br/><form class='form-inline' id='gram-filter'><input type='text' id='gram-cond' placeholder='фильтр (regexp)' value='{if isset($smarty.get.filter)}{$smarty.get.filter|htmlspecialchars}{/if}' class='span2'/> <input type='button' value='OK' class='btn'/></form></th>
    <th>Обновлён</th>
    <th>Автор</th>
    {if $type > $smarty.const.MA_POOLS_STATUS_ANSWERED}<th>{html_options name=moder_id options=$pools.moderators id=moder_id}</th>{/if}
    {if $type > 2}<th>Состояние</th>{/if}
</tr>
{foreach from=$pools.pools item=pool}
<tr>
    <td>{$pool.pool_id}</td>
    <td>
        <a href="?act=samples&amp;pool_id={$pool.pool_id}">{$pool.pool_name|htmlspecialchars}</a>
        {if $user_permission_check_morph}
            {if $type == $smarty.const.MA_POOLS_STATUS_NOT_STARTED}<a href="?act=publish&amp;pool_id={$pool.pool_id}"><i class="icon-play"></i></a>{/if}
            <a href="?act=delete&amp;pool_id={$pool.pool_id}" class='del'><i class="icon-remove"></i></a>
        {/if}
    </td>
    <td>{$pool.grammemes|htmlspecialchars}<br/><span class='small'>{$pool.gram_descr|htmlspecialchars}</span><br/>Оценок: {$pool.users_needed}</td>
    <td>{$pool.updated_ts|date_format:"%a %d.%m.%Y, %H:%M"}</td>
    <td>{$pool.author_name|htmlspecialchars|default:"Робот"}</td>
    {if $pool.status > $smarty.const.MA_POOLS_STATUS_ANSWERED}
        <td>{$pool.moderator_name|default:"&ndash;"}</td>
    {/if}
    {if $pool.status == $smarty.const.MA_POOLS_STATUS_MODERATION}
        <td>проверено: {$pool.moderated_count}/{$pool.instance_count / $pool.users_needed}</td>
    {else}
        <td><span class='{if $pool.instance_count > 0 && $pool.answer_count == $pool.instance_count}bggreen{/if}'>{$pool.answer_count}/{$pool.instance_count} ответов</span></td>
    {/if}
</tr>
{foreachelse}
<tr><td colspan='7'>Нет ни одного пула.</tr>
{/foreach}
</table><br/>
{/block}
