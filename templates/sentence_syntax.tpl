{* Smarty *}
{extends file='common.tpl'}

{block name=styles}
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-notify.css" />
{/block}

{block name=javascripts}
    <script src="bootstrap/js/bootstrap-notify.js"></script>
{/block}

{block name=content}

{literal}
    <script>
    var syntax_groups_json = JSON.parse('{/literal}{$groups.simple|@json_encode|replace:"\"":"\\\""}{literal}');
    var complex_groups_json = JSON.parse('{/literal}{$groups.complex|@json_encode|replace:"\"":"\\\""}{literal}');
    </script>
{/literal}

<script type="text/javascript" src="/js/syntax_groups.js"></script>

<div class="btn-group">
    <a href="?id={$sentence.id}&mode=morph" class="btn {if !isset($smarty.get.mode) || $smarty.get.mode == 'morph'}btn-success{/if}">Морфология</a>
    <a href="?id={$sentence.id}&mode=syntax" class="btn {if isset($smarty.get.mode) && $smarty.get.mode == 'syntax'}btn-success active{/if}">Синтаксис</a>
</div>
<div id="main_annot_syntax" class="row">
    <div class="span7">
        <div id="tokens" data-sentenceid="{$sentence.id}">
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
        <h5>Выделенные именные группы</h5>
        <div class="table_wrapper">
        {include "sentence_syntax_groups.tpl" groups=$groups group_types=$group_types}
        </div>
    </div>
</div>
{if $sentence.prev_id}<div><a href="?id={$sentence.prev_id}&amp;mode=syntax">&lt;&lt; предыдущее предложение</a></div>{/if}
{if $sentence.next_id}<div><a href="?id={$sentence.next_id}&amp;mode=syntax">следующее предложение &gt;&gt;</a></div>{/if}

<div class="notifications top-right"></div>
{/block}
