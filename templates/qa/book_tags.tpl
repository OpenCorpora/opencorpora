{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Ошибки в тегах</h1>
<p>Список обновляется раз в час.</p>
<table border='1' cellspacing='0' cellpadding='3'>
{foreach item=err from=$errata}
<tr>
    <td><a href='{$web_prefix}/books.php?book_id={$err.book_id}'>{$err.book_id}</a></td>
    <td>{$err.tag_name|htmlspecialchars|truncate:100|default:"&nbsp;"}</td>
    {strip}
    <td>{if $err.error_type == 1}Ошибка в годе
        {elseif $err.error_type == 2}Ошибка в дате
        {elseif $err.error_type == 3}Не хватает тега "Автор:"
        {elseif $err.error_type == 4}Ссылка на википроект без версии
        {else}Неизвестная ошибка{/if}
    </td>
    {/strip}
</tr>
{foreachelse}
<tr><td colspan='3'>Список пуст.</td></tr>
{/foreach}
</table>
{/block}
