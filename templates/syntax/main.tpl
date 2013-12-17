{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<table class='table'>
<tr>
    <td></td>
    <td></td>
    <th>Синтаксис</td>
    <th>Анафора</td>
</tr>
{foreach from=$books item=book}
<tr>
    <td><a href="{$web_prefix}/books.php?book_id={$book.id}">{$book.id}</a></td>
    <td>{$book.name|htmlspecialchars}</td>
    <td>
        <div class="btn-group">
            <a href="?act=set_status&amp;status=0&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 0} active{/if}">&mdash;</a>
            <a href="?act=set_status&amp;status=1&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 1} btn-info active{/if}">буду размечать{if $book.status.syntax.total[1] > 0} ({$book.status.syntax.total[1]}){/if}</a>
            <a href="?act=set_status&amp;status=2&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 2} btn-success active{/if}">готово{if $book.status.syntax.total[2] > 0} ({$book.status.syntax.total[2]}){/if}</a>
        </div>
    </td>
</tr>
{/foreach}
</table>
{/block}
