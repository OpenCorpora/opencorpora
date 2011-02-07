{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='russian/header.tpl'}
<div id='content'>
    Всего книг: <b>{$books.num}</b>, <a href='#' class='toggle' onClick='show(byid("book_add")); return false;'>добавить</a>:
    <form id='book_add' style='display:none' method='post' action='?act=add'><input name='book_name' size='30' maxlength='100' value='&lt;Название&gt;'/><input type='hidden' name='book_parent' value='0'/><br/><input type='submit' value='Добавить'/></form>
    <ul>
    {foreach item=book from=$books.list}
        <li><a href='?book_id={$book.id}'>{$book.title}</a></li>
    {/foreach}
    </ul>
</div>
<div id='rightcol'>
{include file='russian/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
