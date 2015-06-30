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
    $('#gram-filter input[type=button]').click(function() {
        var reg = $("#gram-cond").val();
        {/literal}
        location.href = "?type={$type}&filter=" + encodeURIComponent(reg);
        {literal}
    });
});
</script>
{/literal}
<h1>Пулы для морфологической разметки</h1>
{if isset($smarty.get.added)}
<p>Тип добавлен. Когда примеры найдутся, ссылка на них появится на этой странице.</p>
{/if}

{* Type chooser *}
<ul class="nav nav-tabs">
<li class="active"><a href="?type={$smarty.const.MA_POOLS_STATUS_FOUND_CANDIDATES}">поиск примеров</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_NOT_STARTED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_NOT_STARTED}">не опубликованные</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_IN_PROGRESS} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_IN_PROGRESS}">опубликованные</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_ANSWERED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_ANSWERED}">снятые с публикации</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_MODERATION} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_MODERATION}&amp;moder_id={$moder_id}">на модерации</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_MODERATED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_MODERATED}&amp;moder_id={$moder_id}">модерация окончена</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_TO_MERGE} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_TO_MERGE}&amp;moder_id={$moder_id}">в очереди на переливку</a></li>
<li{if $type == $smarty.const.MA_POOLS_STATUS_ARCHIVED} class="active"{/if}><a href="?type={$smarty.const.MA_POOLS_STATUS_ARCHIVED}&amp;moder_id={$moder_id}">в архиве</a></li>
</ul>
{if $user_permission_check_morph}
<p><a href="#" class="pseudo" id="add_pool">Добавить новый тип пулов</a></p>
<form id="f_add" style="display:none" method="post" action="?act=add_type"><table class="table">
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
<tr><td colspan="2"><button class="btn btn-large btn-primary">Добавить</button></tr>
</table></form>
{/if}
<table class="table">
<tr class="borderless">
    <th>ID</th>
    <th>Условия<br/><form class='form-inline' id='gram-filter'><input type='text' id='gram-cond' placeholder='фильтр (regexp)' value='{if isset($smarty.get.filter)}{$smarty.get.filter|htmlspecialchars}{/if}' class='span2'/> <input type='button' value='OK' class='btn'/></form></th>
    <th>Сложность</th>
    <th>Инструкция</th>
    <th>Последний поиск</th>   
</tr>
{foreach from=$types item=t key=type_id}
<tr {if $t.is_auto_mode}class='success muted'{/if}>
    <td>{$type_id}</td>
    <td>
        <a href="?act=candidates&amp;pool_type={$type_id}">{$t.grammemes|htmlspecialchars}</a><br/>
        <span class='small'>{$t.gram_descr|htmlspecialchars}</span>
    </td>
    <td>
        <img src="/assets/img/icon_star_{if $t.complexity == 1}green{else}gray{/if}.png"/>
        <img src="/assets/img/icon_star_{if $t.complexity == 2}yellow{else}gray{/if}.png"/>
        <img src="/assets/img/icon_star_{if $t.complexity == 3}orange{else}gray{/if}.png"/>
        <img src="/assets/img/icon_star_{if $t.complexity == 4}red{else}gray{/if}.png"/>
    </td>
    <td>{if $t.doc_link != ''}<a href="/manual.php?pool_type={$type_id}">есть</a>{/if}</td>
    <td>{if $t.last_search}{$t.last_search|date_format:"%a %d.%m.%Y, %H:%M"}, найдено {$t.found_samples}{else}никогда{/if}</td>
</tr>
{/foreach}
</table><br/>
{/block}
