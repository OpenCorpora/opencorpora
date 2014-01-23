{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    <h3>{$book.title} (id={$book.id})</h3>
    <p>
        <a href="/books.php?book_id={$book.id}" class="btn btn-small">К описанию текста</a>
        <a href="/syntax.php" class="btn btn-small">Вернуться к текстам</a>
    </p>
    {if isset($book.paragraphs)}
        {foreach key=num item=paragraph from=$book.paragraphs}
            <p class="anaph-paragraph">
            {foreach name=s item=sentence from=$paragraph}
                {foreach name=t item=token from=$sentence.tokens}
                    <span id="t{$token.id}">{$token.text|htmlspecialchars}</span>
                {/foreach}
            {/foreach}
            </p>
        {/foreach}
    {else}
        <p>В тексте нет ни одного предложения.</p>
    {/if}
{/block}
