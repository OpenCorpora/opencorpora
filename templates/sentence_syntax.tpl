{* Smarty *}
{extends file='common.tpl'}
{block name=content}

<script type="text/javascript" src="/js/syntax_groups.js"></script>

<div class="btn-group">
    <a href="?id={$sentence.id}&mode=morph" class="btn {if !isset($smarty.get.mode) || $smarty.get.mode == 'morph'}btn-success{/if}">Морфология</a>
    <a href="?id={$sentence.id}&mode=syntax" class="btn {if isset($smarty.get.mode) && $smarty.get.mode == 'syntax'}btn-success{/if}">Синтаксис</a>
</div>
<div id="main_annot_syntax">
    <div id="tokens">
    {foreach item=token from=$sentence.tokens}
        <span data-tid="{$token.tf_id}" class="token">{$token.tf_text|htmlspecialchars}</span>
    {/foreach}
    </div>
    <div id="selection_info"><form class="form-inline">
        Выделено <b>0</b><span id="new_group" style="display: none">, <button type="button" id="add0" class="btn btn-small add-group">Создать группу</button><button type="button" id="add1" class="btn btn-small btn-primary add-group">Создать!</button></span>
    <select id="group_type">
        <option value="0">Без типа</option>
        {foreach from=$group_types item=group key=gid}
        <option value="{$gid}">{$group|htmlspecialchars}</option>
        {/foreach}
    </select>
    <button type="button" id="save_group_roots" class="btn btn-small btn-primary">Сохранить вершины именных групп</button>
    </form></div>
</div>
{*$sentence|var_dump*}
{/block}
