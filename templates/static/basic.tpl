{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
{if isset($title)}<h1>{$title}</h1>{/if}
{$content}
{/block}
