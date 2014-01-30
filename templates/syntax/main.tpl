{* Smarty *}
{extends file='common.tpl'}
{block name=content}
Всего токенов &mdash; <b>{$page.token_count}</b>
<table class='table'>
<tr>
    <td></td>
    <td></td>
    <th>Синтаксис</td>
    <th>ИГ</th>
    <th>Анафора</td>
</tr>
{foreach from=$page.books item=book}
<tr>
    <td><a href="{$web_prefix}/books.php?book_id={$book.id}">{$book.id}</a></td>
    <td>{$book.name|htmlspecialchars}</td>
    <td>
        <div class="btn-group">
            <a href="?act=set_status&amp;status=0&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 0} active{/if}">&mdash;</a>
            <a href="?act=set_status&amp;status=1&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 1} btn-info active{/if}">буду размечать{if $book.status.syntax.total[1] > 0} ({$book.status.syntax.total[1]}){/if}</a>
            <a href="?act=set_status&amp;status=2&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 2} btn-success active{/if}">готово{if $book.status.syntax.total[2] > 0} ({$book.status.syntax.total[2]}){/if}</a>
            <a href="?act=set_moderated&amp;book_id={$book.id}" class="btn btn-small" {if $book.syntax_moder_id}disabled{/if}>буду модерировать</a></td>
        </div>
    </td>
    <td>
        <a href="{$web_prefix}/sentence.php?id={$book.first_sentence_id}" class="btn btn-small">К 1-ому предложению &raquo;</a>
    <td><a href="{$web_prefix}/books.php?book_id={$book.id}&amp;act=anaphora" class="btn btn-small">Размечать &raquo;</a></td>
</tr>
{/foreach}
</table>
{/block}
