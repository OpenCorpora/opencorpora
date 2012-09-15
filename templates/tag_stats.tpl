{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>{t}Статистика{/t}</h1>
<ul class="nav nav-tabs">
    <li><a href="?page=stats">{t}Общая{/t}</a></li>
    <li class="active"><a href="?page=tag_stats">{t}По тегам{/t}</a></li>
</ul>
<ul class="nav clearfix">
    {foreach from=$stats item=i key=gname}
        <li class="pull-left" style="padding-right:7px;"><a href="#{$gname}">#{$gname}</a></li>
    {/foreach}
</ul>
<table class="table">
{foreach from=$stats item=group key=gname}
<tr><th><a name="{$gname}">{$gname}</a></th><th>текстов</th><th>слов</th></tr>
{foreach from=$group item=elem}
<tr><td>{$elem.value|htmlspecialchars}</td><td>{$elem.texts}</td><td>{$elem.words}</td></tr>
{/foreach}
{/foreach}
</table>
{/block}
