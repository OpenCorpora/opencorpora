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
            <a href="?act=set_status&amp;status=0&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 0} active{/if} btn-small">&mdash;</a>
            <a href="?act=set_status&amp;status=1&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 1} btn-info active{/if} btn-small">буду размечать{if $book.status.syntax.total[1] > 0} ({$book.status.syntax.total[1]}){/if}</a>
            <a href="?act=set_status&amp;status=2&amp;book_id={$book.id}" class="btn{if $book.status.syntax.self == 2} btn-success active{/if} btn-small">готово{if $book.status.syntax.total[2] > 0} ({$book.status.syntax.total[2]}){/if}</a>
            {if $book.status.syntax.moderated}
            <button disabled class="btn btn-small btn-success">Отмодерировано</button>
            {elseif not $book.syntax_moder_id}
            <a href="?act=set_moderated&amp;book_id={$book.id}" class="btn btn-small">буду модерировать</a>
            {elseif $smarty.session.user_id eq $book.syntax_moder_id}
            <button disabled class="btn btn-small btn-success">Вы - модератор</button>
            {else}
            <button disabled class="btn btn-small btn-info">На модерации</button>
            {/if}
        </div>
    </td>
    <td>
        <a href="{$web_prefix}/sentence.php?mode=syntax&amp;id={$book.first_sentence_id}" class="btn btn-mini">К 1-ому предложению&nbsp;&raquo;</a>
    <td><a href="{$web_prefix}/books.php?book_id={$book.id}&amp;act=anaphora" class="btn btn-mini"{if !$book.status.syntax.moderated} disabled="disabled"{/if}>Размечать</a></td>
</tr>
{/foreach}
</table>
{/block}
