{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Администрирование игровой части</h1>
<h2>Бейджи</h2>
<form action="?act=save" method="post">
{foreach from=$badges item=badge}
<div class="pull-left" style="border: 1px #ddd solid; margin-right: 10px; padding: 5px">
    <div><img style="margin-bottom: 5px; cursor: help" src="{if $badge.image}img/badges/{$badge.image}-100x100.png{else}http://placehold.it/100x100{/if}" title="{$badge.description|htmlspecialchars}"></div>
    <div align="center">
        <input type="text" name="badge_name[{$badge.id}]" value="{$badge.name|htmlspecialchars}"/><br/>
        <textarea name="badge_descr[{$badge.id}]">{$badge.description|htmlspecialchars}</textarea>
    </div>
</div>
{/foreach}
</form>
{/block}
