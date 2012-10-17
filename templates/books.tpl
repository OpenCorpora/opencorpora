{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    <h1>Тексты</h1>
    <p>Всего книг: <b>{$books.num}</b>{if $user_permission_adder},
    <a href='#' class='pseudo' onClick='$("#book_add").show(); return false;'>добавить</a>:
    <form id='book_add' style='display:none' method='post' action='?act=add' class="form-inline"><input type="text" name='book_name' class="span3" maxlength='100' placeholder="Название"><input type='hidden' name='book_parent' value='0'> <button type='submit' class="btn">Добавить</button></form>
    {/if}
    <ul>
    {foreach item=book from=$books.list}
        <li><a href='?book_id={$book.id}'>{$book.title}</a></li>
    {/foreach}
    </ul>
{/block}
