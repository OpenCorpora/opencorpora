{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h3>Разметка именованных сущностей</h3>
<!-- Всего токенов &mdash; <b>{$page.token_count}</b> -->
<table class='table'>
{foreach from=$page item=book}
<tr>
    <td><a href="{$web_prefix}/books.php?book_id={$book.id}">{$book.id}</a></td>
    <td>{$book.name|htmlspecialchars}</td>
    <td><a href="{$web_prefix}/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small">Размечать</a></td>
</tr>
{/foreach}
</table>
{/block}
