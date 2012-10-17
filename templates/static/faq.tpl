{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>О проекте</h1>
<ul class="nav nav-tabs">
    <li><a href="{$web_prefix}/?page=about">Описание проекта</a></li>
    <li><a href="{$web_prefix}/?page=team">Участники</a></li>
    <li><a href="{$web_prefix}/?page=publications">Публикации</a></li>
    <li class="active"><a href="{$web_prefix}/?page=faq">FAQ</a></li>
</ul>
{if isset($title)}<h2>{$title}</h2>{/if}
{$content}
{/block}
