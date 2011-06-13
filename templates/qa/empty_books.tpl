{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Пустые тексты</h1>
<p>Это тексты, к которым не приписано ни одного раздела и ни одного предложения.</p>
<p>Список обновляется при каждом обращении к этой странице.</p>
<ol>
{foreach item=book from=$books}
<li><a href='{$web_prefix}/books.php?book_id={$book.id}'>{$book.name|htmlspecialchars}</a></li>
{foreachelse}
<p>Список пуст.</p>
{/foreach}
</ol>
{/block}
