{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function() {
    $('#btn_add').click(function() {
        $('#add_badge').show();
        $(this).hide();
    });
});
{/literal}
</script>
<h1>Администрирование игровой части</h1>
<h2>Бейджи</h2>
<form action="?act=save" method="post">
<button class="btn btn-primary">Сохранить</button>
<button type="button" class="btn btn-info" id="btn_add">Добавить</button><br/>
<div class="pull-left" style="display:none" id="add_badge">
    <div><img style="margin-bottom: 5px; cursor: help" src="http://placehold.it/100x100"></div>
    <div align="center">
        <input type="text" name="badge_name[-1]" class="pull-left" placeholder="название"/>
        <input type="text" name="badge_group[-1]" class="pull-left span1" value="0"/><br/>
        <input type="text" name="badge_image[-1]"/>-100x100.png<br/>
        <textarea name="badge_descr[-1]" class="pull-left" placeholder="Описание"></textarea>
    </div>
</div>
{foreach from=$badges item=badge}
<div class="pull-left" style="border: 1px #ddd solid; margin-right: 10px; padding: 5px" class="">
    <div><img style="margin-bottom: 5px; cursor: help" src="{if $badge.image}img/badges/{$badge.image}-100x100.png{else}http://placehold.it/100x100{/if}" title="{$badge.description|htmlspecialchars}"></div>
    <div align="center">
        <input type="text" name="badge_name[{$badge.id}]" class="pull-left" value="{$badge.name|htmlspecialchars}"/>
        <input type="text" name="badge_group[{$badge.id}]" class="pull-left span1" value="{$badge.group}"/><br/>
        <input type="text" name="badge_image[{$badge.id}]" value="{$badge.image|htmlspecialchars}"/>-100x100.png<br/>
        <textarea name="badge_descr[{$badge.id}]" class="pull-left">{$badge.description|htmlspecialchars}</textarea>
        <input type="text" class="pull-left span1" value="{$badge.id}" readonly/><br/>
    </div>
</div>
{/foreach}
</form>
{/block}
