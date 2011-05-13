{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    {t}Всего книг{/t}: <b>{$books.num}</b>{if $user_permission_adder},
    <a href='#' class='toggle' onClick='$("#book_add").show(); return false;'>{t}добавить{/t}</a>:
    <form id='book_add' style='display:none' method='post' action='?act=add'><input name='book_name' size='30' maxlength='100' value='&lt;{t}Название{/t}&gt;'/><input type='hidden' name='book_parent' value='0'/><br/><input type='submit' value='{t}Добавить{/t}'/></form>
    {/if}
    <ul>
    {foreach item=book from=$books.list}
        <li><a href='?book_id={$book.id}'>{$book.title}</a></li>
    {/foreach}
    </ul>
{/block}
