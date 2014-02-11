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

                    <span id="t{$token.id}" data-tid="{$token.id}" class="anaph-token {if in_array($token.id, $sentence.props)}anaph-prop {elseif $token.groups.simple or $token.groups.complex}anaph-head{/if}">{$token.text|htmlspecialchars}</span>
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
        <table class="table anaph-table">
        {foreach $anaphora as $a}
            <tr>
                <td class="actions"><i class="icon icon-remove remove-anaphora" alt="Удалить" data-aid="{$a.ref_id}"></i></td>
                <td class="anaph-text" data-tid="{$a.token_id}">{$a.token}</td>
                <td class="group-text" data-gid="{$a.group_id}" data-tokens="{$a.group_tokens|json_encode}">{$a.group_text}</td>
            </tr>
        {foreachelse}
            <tr class="tr-stub"><td colspan="3" class="actions">Пока не выделено ни одной анафоры.</td></tr>
        {/foreach}
            <tr class="tr-tpl">
                <td class="actions"><i class="icon icon-remove remove-anaphora" alt="Удалить"></i></td>
                <td class="anaph-text"></td>
                <td class="group-text"></td>
            </tr>
        </table>
    </div>
</div>

<div class="notifications top-right"></div>
{/block}

{block name="javascripts"}
{literal}
    <script src="bootstrap/js/bootstrap-notify.js"></script>
    <script src="js/anaphora.js"></script>
    <script>
    var syntax_groups_json = JSON.parse('{/literal}{$token_groups|@json_encode|replace:"\"":"\\\""}{literal}');
    var group_types = JSON.parse('{/literal}{$group_types|@json_encode|replace:"\"":"\\\""}{literal}');
    </script>
{/literal}
{/block}

{block name=styles}
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-notify.css" />
{/block}
