{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>Виды связей</h1>
<ol>
{foreach item=name key=typeid from=$data}
    <li><a href="?act=links&type={$typeid}">{$name}</a></li>
{/foreach}
</ol>
{/block}
