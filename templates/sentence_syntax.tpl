{* Smarty *}
{extends file='common.tpl'}

{block name=styles}
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap-notify.css" />
{/block}

{block name=javascripts}
    <script src="/assets/js/bootstrap-notify.js"></script>
    <script type="text/javascript" src="/assets/js/syntax_groups.js"></script>
    {block name=inject_groups_json}
    {literal}
        <script>
        var syntax_groups_json = JSON.parse('{/literal}{$groups.simple|@json_encode|replace:"\"":"\\\""}{literal}');
        var complex_groups_json = JSON.parse('{/literal}{$groups.complex|@json_encode|replace:"\"":"\\\""}{literal}');
        </script>
    {/literal}
    {/block}
{/block}

{block name=content}

<div class="btn-group">
    <a href="?id={$sentence.id}&mode=morph" class="btn {if !isset($smarty.get.mode) || $smarty.get.mode == 'morph'}btn-success{/if}">Морфология</a>
    <a href="?id={$sentence.id}&mode=syntax" class="btn {if isset($smarty.get.mode) && $smarty.get.mode == 'syntax'}btn-success active{/if}">Синтаксис {block name=button_caption}{/block}</a>
<a href="/books.php?book_id={$sentence.book_id}" class="btn">Вернуться к списку предложений</a>
                <a href="/syntax.php" class="btn">Вернуться к текстам</a>

</div>
{block name=syntax_heading}{/block}
<div class="pagination">
    <ul>
        <li>
            {if $sentence.prev_id}<a href="?id={$sentence.prev_id}&amp;mode=syntax" >&lt;&lt;</a>
            {else}<span>&lt;&lt;</span>{/if}
        </li>
        <li class="disabled"><span>Перейти к предложению</span></li>
        <li>
            {if $sentence.next_id}<a href="?id={$sentence.next_id}&amp;mode=syntax">&gt;&gt;</a>
            {else}<span>&gt;&gt;</span>{/if}
        </li>
        {if !$sentence.next_id}
        <li><a href="/syntax.php?act=finish_moder&amp;book_id={$sentence.book_id}" class="btn-success" onclick="return confirm('Закончить модерацию?')">Закончить модерацию</a></li>
        {/if}
    </ul>
</div>

<div class="main_annot_syntax row" id="my_syntax">
    <div class="span7">
        <div class="tokens" data-sentenceid="{$sentence.id}" data-userid="{$smarty.session.user_id}">
            {foreach item=token from=$sentence.tokens}<span data-tid="{$token.tf_id}" class="token">{$token.tf_text|htmlspecialchars}</span>{/foreach}
        </div>
        <div id="selection_info">
            <form class="form-inline">
            Выделено <b>0</b><span id="new_group" style="display: none">, <button type="button" id="add0" class="btn btn-small add-group">Создать группу</button><button type="button" id="add1" class="btn btn-small btn-primary add-group">Создать!</button></span>
                <select id="group_type">
                <option value="0">Без типа</option>
                {foreach from=$group_types item=group key=gid}
                    <option value="{$gid}">{$group|htmlspecialchars}</option>
                {/foreach}
                </select>
            </form>
        </div>
    </div>
    <div id="groups_table" class="span4">
        <h5>Выделенные группы <a href="#" class="small toggle show-dummy">показать искусственные</a></h5>
        <div class="table_wrapper">
        {include "sentence_syntax_groups.tpl" groups=$groups group_types=$group_types}
        </div>
    </div>
</div>
{block name=syntax_bottom}{/block}

<div class="notifications top-right"></div>
{/block}
