{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Сохранённые копии текстов источников</h1>
<p>Список обновляется при каждом обращении к этой странице.</p>
<table class="table">
<tr><th>Текст</th><th>url</th><th>Файл</th></tr>
{foreach item=obj from=$urls}
<tr{if !$obj.filename || !$obj.exists} class="error"{/if}>
    <td><a href="{$web_prefix}/books.php?book_id={$obj.book_id}">{$obj.book_name|htmlspecialchars}</a></td>
    <td><a href="{$obj.url}">{$obj.url|truncate}</a></td>
    <td><a href="{$web_prefix}/files/saved/{$obj.filename}.html">{$obj.filename}</a>{if $obj.filename && !$obj.exists}, не существует{/if}</td>
</tr>
{/foreach}
</table>
{/block}
