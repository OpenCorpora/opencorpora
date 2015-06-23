{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    {assign var=colorStep value=2}
    <h3>{$book.title} (id={$book.id})</h3>
    <div class="buttons-container">
        <a href="/books.php?book_id={$book.id}" class="btn btn-small btn-link">К описанию текста</a>
        <a href="/ner.php" class="btn btn-link btn-small">Вернуться к текстам</a>
        <div class="btn-group" data-toggle="buttons-radio">
            <button class="ner-mode-basic btn btn-small {if not $use_fast_mode}active{/if}">Разметка кликом</button>
            <button class="ner-mode-fast btn btn-small {if $use_fast_mode}active{/if}">Разметка выделением</button>
        </div>
        <a class="btn btn-primary btn-small" href="/ner.php?act=manual" target="_blank"><i class="icon-info-sign icon-white"></i> Инструкция</a>
    </div>
    {if isset($book.paragraphs)}
        {foreach name=b key=num item=paragraph from=$book.paragraphs}
            <div class="row ner-row">
                <div class="span4 my-comments">
                </div>
                <div class="span8">
                    <div class="ner-paragraph-wrap {if $paragraph.disabled }ner-disabled{elseif $paragraph.mine}ner-mine{/if}" {if isset($paragraph.annotation_id)}data-annotation-id="{$paragraph.annotation_id}"{/if}>
                        <p class="ner-paragraph" data-par-id="{$paragraph.id}">
                        {foreach name=s item=sentence from=$paragraph.sentences}
                            {foreach name=t item=token from=$sentence.tokens}{capture name="token"}<span

                                    id="t{$token.id}"
                                    data-tid="{$token.id}"
                                {if $paragraph.ne_by_token[$token.id]}
                                    data-entity-id="{$paragraph.ne_by_token[$token.id].entity_id}"
                                {/if}
                                    class="ner-token
                                        {if $paragraph.ne_by_token[$token.id]}
                                            ner-entity
                                            {if count($paragraph.ne_by_token[$token.id]['tags']) > 1}
                                                ner-multiple-types
                                            {else}
                                                border-bottom-palette-{$paragraph.ne_by_token[$token.id]['tags'][0][0] * $colorStep}
                                            {/if}
                                        {/if}"

                                >{$token.text|htmlspecialchars} </span>{/capture}{$smarty.capture.token|strip:" "}{/foreach}

                        {/foreach}
                        </p>
                        <div class="ner-paragraph-controls">
                            <!--button class="btn btn-primary ner-btn-start" data-par-id="{$paragraph.id}">Я буду размечать</button-->
                            <button class="btn btn-success ner-btn-finish pull-right" data-par-id="{$paragraph.id}">Сохранить</button>
                        </div>
                        <div class="clearfix"></div>
                        <div class="comment-marker" data-paragraph-id="{$paragraph.id}"><span>{if $paragraph.comments|@count>0}{$paragraph.comments|@count}{else}+{/if}</span></div>
                        <div class="comment-list-stub" data-paragraph-id="{$paragraph.id}">
                            {foreach $paragraph.comments as $comment}
                                <div class="comment-wrap">
                                    <div class="comment-text">{$comment.comment}</div>
                                    <div class="comment-date">{$comment.created|date_format:"%e %b, %k:%M"}</div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
                <div class="span4 ner-table-wrap {if $paragraph.disabled }ner-disabled{elseif $paragraph.mine}ner-mine{/if}">

                    <table class="table ner-table table-condensed" data-par-id="{$paragraph.id}">
                        {foreach $paragraph.named_entities as $ne}
                            <tr data-entity-id="{$ne.id}">
                                <td class="ner-entity-actions"><i class="icon icon-remove ner-remove" data-entity-id={$ne.id}></i></td>
                                <td class="ner-entity-text span4">
                                {foreach $ne.tokens as $token}{$token[1]} {/foreach}
                                </td>
                                <td class="ner-entity-type span3">
                                {if $paragraph.mine}
                                    <select class="selectpicker show-menu-arrow pull-right" data-width="140px" data-style="btn-small" data-entity-id="{$ne.id}" multiple>
                                    {foreach $types as $type}
                                        <option data-content="<span class='label label-palette-{$type.id * $colorStep}'>{$type.name}</span>" {if in_array(array_values($type), $ne.tags)}selected{/if}>{$type.id}</option>
                                    {/foreach}
                                    </select>
                                {else}
                                    {foreach $ne.tags as $tag}
                                        <span class="label label-palette-{$tag[0] * $colorStep}">{$tag[1]}</span>
                                    {/foreach}
                                {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
        {/foreach}
    {else}
        <div class="row">
            <p>В тексте нет ни одного предложения.</p>
        </div>
    {/if}
<div class="row">
    <div class="span8">
        <div class="buttons-container">
            <a href="/books.php?book_id={$book.id}" class="btn btn-small btn-link">К описанию текста</a>
            <a href="/ner.php" class="btn btn-link btn-small">Вернуться к текстам</a>
            <button class="btn btn-small btn-success ner-btn-finish-all">Сохранить всё</button>
        </div>
    </div>
</div>

<div class="popover types-popover top floating-block">
    <div class="arrow"></div>
    <h3 class="popover-title">Выберите один из типов:</h3>
    <div class="popover-content">
        <div class="btn-group type-selector" data-toggle="buttons-checkbox">
            {foreach $types as $type}
                {if $type.name !== 'phrase'}
                <button class="btn btn-small btn-palette-{$type.id * $colorStep}" data-type-id="{$type.id}" data-hotkey="{$type.name|substr:0:1}">{$type.name}</button>{/if}
            {/foreach}
                <button class="btn btn-small btn-palette-5 composite-type" data-type-ids="2,3" data-hotkey="i">loc-org</button>
        </div>
    </div>
</div>

<div class="templates">
    <table class="table-stub">
    <tr class="tr-template">
        <td class="ner-entity-actions"><i class="icon icon-remove ner-remove"></i></td>
        <td class="ner-entity-text span4"></td>
        <td class="ner-entity-type span3">
            <select class="selectpicker-tpl show-menu-arrow pull-right" data-width="140px" data-style="btn-small" multiple>
            {foreach $types as $type}
                <option data-content="<span class='label label-palette-{$type.id * $colorStep}'>{$type.name}</span>">{$type.id}</option>
            {/foreach}
            </select>
        </td>
    </tr>
    </table>
    <div class="comment-add-stub">
        <div class="comment-add">
            <textarea rows="2"></textarea>
            <button class="btn btn-primary btn-block btn-small btn-comment-add">Отправить</button>
        </div>
    </div>
</div>
{/block}

{block name="javascripts"}
{literal}
    <script src="/assets/js/bootstrap.select.min.js"></script>
    <script src="/assets/js/rangy-core.js"></script>
    <script src="/assets/js/mousetrap.min.js"></script>
    <script src="/assets/js/ner.js"></script>
    <script src="/assets/js/ne_comments.js"></script>
{/literal}
{/block}

{block name=styles}
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap-select.min.css" />
{/block}
