{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<p>{$error_text}</p>
{if isset($error_msg)}<p>{$error_msg}</p>{/if}
{/block}
