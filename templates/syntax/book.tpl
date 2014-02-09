{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    <h3>{$book.title} (id={$book.id})</h3>
    <p>
        <a href="/books.php?book_id={$book.id}" class="btn btn-small">К описанию текста</a>
        <a href="/syntax.php" class="btn btn-small">Вернуться к текстам</a>
    </p>
    <div class="row">
        <div class="span8">
    {if isset($book.paragraphs)}
        {foreach key=num item=paragraph from=$book.paragraphs}
            <p class="anaph-paragraph">
            {foreach name=s item=sentence from=$paragraph}
                {foreach name=t item=token from=$sentence.tokens}

                    <span id="t{$token.id}" data-tid="{$token.id}" class="{if in_array($token.id, $sentence.props)}anaph-prop {elseif $token.groups.simple or $token.groups.complex}anaph-head{/if}">{$token.text|htmlspecialchars}</span>
                {/foreach}
            {/foreach}
            </p>
        {/foreach}
    {else}
        <p>В тексте нет ни одного предложения.</p>
    {/if}
    </div>
    <div class="span4">
        <h4>Анафоры в тексте</h4>
        <p>Пока не выделено ни одной анафоры.</p>
    </div>
{/block}

{block name="javascripts"}
{literal}
    <script src="/js/anaphora.js"></script>
    <script>
    var syntax_groups_json = JSON.parse('{/literal}{$token_groups|@json_encode|replace:"\"":"\\\""}{literal}');
    var group_types = JSON.parse('{/literal}{$group_types|@json_encode|replace:"\"":"\\\""}{literal}');
    </script>
{/literal}
{/block}
