{* Smarty *}
{extends file='common.tpl'}
{block name=content}
  <div class="buttons-container">
    <a href="/ner.php" class="btn btn-link btn-small">Вернуться к текстам</a>
    <div class="btn-group" data-toggle="buttons-radio">
      <button class="ner-mode-basic btn btn-small {if not $use_fast_mode}active{/if}">Разметка кликом</button>
      <button class="ner-mode-fast btn btn-small {if $use_fast_mode}active{/if}">Разметка выделением</button>
    </div>
    <a class="btn btn-primary btn-small" href="/ner.php?act=manual&id={$current_guideline}" target="_blank"><i class="icon-info-sign icon-white"></i> Инструкция {$possible_guidelines[$current_guideline]}</a>
    {if $is_moderator}
    <span class="label label-important">Я &mdash; модератор этого текста</span>
    {/if}
  </div>
  {if isset($book.paragraphs)}
    {foreach name=b key=num item=paragraph from=$book.paragraphs}
      {if $is_moderator}
        {include file="ner/_partials/paragraph-moderator.tpl" num=$num paragraph=$paragraph name=$b}
      {else}
        {include file="ner/_partials/paragraph.tpl" num=$num paragraph=$paragraph name=$b}
      {/if}
    {/foreach}
    <script type="text/javascript">
      var PARAGRAPHS = {$book.paragraphs|json_encode};
      var MENTION_TYPES = {$mention_types|json_encode};
      var ENTITY_TYPES = {$entity_types|json_encode};

    </script>
  {else}
    <div class="row">
      <p>В тексте нет ни одного предложения.</p>
    </div>
  {/if}
<div class="row">
  <div class="span8">
    <div class="buttons-container">
      <a href="/ner.php" class="btn btn-link btn-small">Вернуться к текстам</a>
      {if !$is_moderator}
        <button class="btn btn-small btn-success ner-btn-finish-all">Сохранить всё</button>
      {/if}
    </div>
  </div>
</div>

<div class="popover types-popover top floating-block">
  <div class="arrow"></div>
  <h3 class="popover-title">Выберите один из типов:</h3>
  <div class="popover-content">
    <div class="btn-group type-selector ner-type-selector" data-toggle="buttons-checkbox">
      {foreach $entity_types as $type}
        <button class="btn btn-small btn-palette-{$type.color}" data-type-id="{$type.id}" data-hotkey="{$type.name|substr:0:1}">{$type.name}</button>
      {/foreach}
    </div>
  </div>
</div>

<div class="popover types-popover top m-floating-block">
  <div class="arrow"></div>
  <h3 class="popover-title">Новое упоминание:</h3>
  <div class="popover-content">
    <div class="btn-group type-selector mention-type-selector" data-toggle="buttons-checkbox">
      {foreach $mention_types as $type}
        <button class="btn btn-small btn-palette-{$type.color}" data-type-id="{$type.id}">{$type.name}</button>
      {/foreach}
    </div>
  </div>
</div>

<div class="templates">
  <table class="table-stub">
  <tr class="tr-template">
    <td class="ner-entity-actions"><i class="icon icon-remove ner-remove remove-ner"></i></td>
    <td class="ner-entity-text span4">
      <span class="ner-entity-text-wrap"></span>
    </td>
    <td class="ner-entity-type span3">
      <select class="selectpicker-tpl show-menu-arrow pull-right" data-width="140px" data-style="btn-small" multiple>
      {foreach $entity_types as $type}
        <option data-content="<span class='label label-palette-{$type.color}'>{$type.name}</span>">{$type.id}</option>
      {/foreach}
      </select>
    </td>
  </tr>
  </table>

  <table class="m-table-stub">
  <tr class="m-tr-template">
    <td class="ner-mention-actions"><i class="icon icon-remove ner-remove remove-mention"></i></td>
    <td class="ner-mention-text span4"></td>
    <td class="ner-mention-type span3">
      <select class="selectpicker-tpl show-menu-arrow pull-right" data-width="140px" data-style="btn-small">
      {foreach $mention_types as $type}
        <option data-content="<span class='label label-palette-{$type.color}'>{$type.name}</span>">{$type.id}</option>
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
  <script src="/assets/js/ner.js?5"></script>
  {/literal}{if $is_moderator}
    <script src="/assets/js/ner-moderator.js"></script>
  {/if}{literal}
  <script src="/assets/js/mentions.js?5"></script>
  <script src="/assets/js/ne_comments.js"></script>
{/literal}
{/block}

{block name=styles}
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap-select.min.css" />
{/block}
