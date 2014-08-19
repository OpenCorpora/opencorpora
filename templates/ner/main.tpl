{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<div class="clearfix">
    <div class="pull-right">
        <a class="btn btn-primary" href="?act=manual" target="_blank"><i class="icon-info-sign icon-white"></i> Инструкция</a>
    </div>
</div>
<h3>Разметка именованных сущностей</h3>
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
