{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>О проекте</h1>
<ul class="nav nav-tabs">
    <li><a href="/?page=about">Описание проекта</a></li>
    <li><a href="/?page=team">Участники</a></li>
    <li><a href="/?page=publications">Публикации</a></li>
    <li class="active"><a href="/?page=faq">FAQ</a></li>
</ul>
{if isset($title)}<h2>{$title}</h2>{/if}
{$content}
{/block}
