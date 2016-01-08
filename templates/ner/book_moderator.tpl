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
  </div>
  {if isset($book.paragraphs)}
    {foreach name=b key=num item=paragraph from=$book.paragraphs}
      <div class="row ner-row {if $paragraph.disabled }ner-row-disabled{/if}
      {if $paragraph.mine}ner-row-mine{/if}">
        <div class="span4 my-comments">
        </div>
        <div class="span8">
          <div class="ner-paragraph-wrap {if $paragraph.disabled }ner-disabled{elseif $paragraph.mine}ner-mine{/if}" {if isset($paragraph.annotation_id)}data-annotation-id="{$paragraph.annotation_id}"{/if}>
            <p class="ner-paragraph" data-par-id="{$paragraph.id}">
            {foreach name=s item=sentence from=$paragraph.sentences}
              {foreach name=t item=token from=$sentence.tokens}{capture name="token"}
                <span
                  id="t{$token.id}"
                  data-tid="{$token.id}"
                  data-sentid="{$sentence.id}"
                  class="ner-token">
                  <span class="ner-token-text">{$token.text|htmlspecialchars}</span>
                  <span class="ner-token-borders"></span>
                </span>{/capture}{$smarty.capture.token|strip:" "}{/foreach}
            {/foreach}
            </p>
            <div class="ner-paragraph-controls">
              <button class="btn btn-success ner-btn-finish pull-right" data-par-id="{$paragraph.id}">Закончить разметку абзаца</button>
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
          <div class="tabbable dragged-up">
            <ul class="nav nav-tabs small-tabs">
              <li class="active"><a href="#tab-entities-{$paragraph.id}" data-toggle="tab">Спаны</a></li>
              <li><a href="#tab-mentions-{$paragraph.id}" data-toggle="tab" class="tab-opener">Упоминания</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab-entities-{$paragraph.id}">
                <table class="table ner-table table-condensed" data-par-id="{$paragraph.id}">
                {foreach $paragraph.named_entities as $ne}
                  <tr data-entity-id="{$ne.id}">
                    <td class="ner-entity-actions"><i class="icon icon-remove ner-remove remove-ner" data-entity-id={$ne.id}></i></td>

                    <td class="ner-entity-text span4">
                      {if count($ne.mention_ids) > 0}
                        {foreach $ne.mention_ids as $i => $id}
                          <span class="ner-entity-mention-link
                          label-palette-{$mention_types[$ne.mention_types[$i]]['color']}"
                          data-mention-id="{$id}"></span>
                        {/foreach}
                      {/if}
                      <span class="ner-entity-text-wrap">
                      {foreach $ne.tokens as $i => $token}{$token[1]} {/foreach}
                      </span>
                    </td>

                    <td class="ner-entity-type span3">
                    {if $paragraph.mine}
                      <select class="selectpicker selectpicker-not-initialized
                      show-menu-arrow pull-right" data-width="140px" data-style="btn-small" data-entity-id="{$ne.id}" multiple>

                      {foreach $entity_types as $type}
                        <option data-content="<span class='label label-palette-{$type.color}'>{$type.name}</span>" {if in_array($type.id, $ne.tag_ids)}selected{/if}>{$type.id}</option>
                      {/foreach}
                      </select>
                    {else}
                      {foreach $ne.tags as $tag}
                        <span class="label label-palette-{$entity_types[$tag[0]].color}">{$tag[1]}</span>
                      {/foreach}
                    {/if}
                    </td>
                  </tr>
                {/foreach}
                </table>
              </div>

              <div class="tab-pane" id="tab-mentions-{$paragraph.id}">
              <table class="table mentions-table table-condensed" data-par-id="{$paragraph.id}">
              {foreach $paragraph.mentions as $id => $mention}
                {if $id == 0}{continue}{/if}
                <tr data-mention-id="{$id}">
                  <td class="ner-mention-actions"><i class="icon icon-remove ner-remove remove-mention" data-mention-id={$id}></i></td>
                  <td class="ner-mention-text span4">
                    {foreach $mention['entities'] as $ne}
                      [{foreach $ne.tokens as $i => $token}{$token[1]}{if $i
                      < count($ne.tokens)-1} {/if}{/foreach}]
                    {/foreach}
                  </td>
                  <td class="ner-mention-type span3">
                  {if $paragraph.mine}
                    <select class="selectpicker selectpicker-not-initialized
                    show-menu-arrow pull-right" data-width="140px" data-style="btn-small" data-mention-id="{$id}">
                    {foreach $mention_types as $type}
                      <option data-content="<span class='label label-palette-{$type.color}'>{$type.name}</span>" {if $type['id'] == $mention['type']}selected{/if}>{$type.id}</option>
                    {/foreach}
                    </select>
                  {else}
                    {foreach $mention_types as $type}
                      {if $type['id'] == $mention['type']}
                      <span class="label label-palette-{$type['color']}">{$type['name']}</span>
                      {/if}
                    {/foreach}
                  {/if}
                  </td>
                </tr>
              {/foreach}
              </table>
              </div>
            </div>
          </div>
        </div>
      </div>
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
      <!--button class="btn btn-small btn-success ner-btn-finish-all">Сохранить всё</button-->
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
  <script src="/assets/js/ner-moderator.js?6"></script>
  <script src="/assets/js/mentions.js?5"></script>
  <script src="/assets/js/ne_comments.js"></script>
{/literal}
{/block}

{block name=styles}
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap-select.min.css" />
{/block}
