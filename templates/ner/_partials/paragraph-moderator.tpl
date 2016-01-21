<div class="tabbable">
  <ul class="nav nav-tabs small-tabs">
    <li class="active"><a href="#annotation-layer-moderator-{$paragraph.id}" data-toggle="tab">Моя разметка</a></li>
    {foreach $paragraph.all_annotations as $uid => $his_annotation}
    <li><a href="#annotation-layer-{$uid}-{$paragraph.id}" data-toggle="tab" class="tab-opener upper-tab-nav" data-user-id="{$uid}">{$his_annotation.user_shown_name|truncate:18:'..':true:true}
        (<b title="спанов">{count($his_annotation.named_entities)}</b> |
        <b title="упоминаний">{count($his_annotation.mentions)}</b>)
      </a></li>
    {/foreach}
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="annotation-layer-moderator-{$paragraph.id}">
      <div class="row ner-row
      {if $paragraph.annotation_id}ner-row-mine{/if}">
        <div class="span4 my-comments">
        </div>
        <div class="span8">
          <div class="ner-paragraph-wrap moderator-paragraph-wrap {if $paragraph.annotation_id}ner-mine{/if}" {if $paragraph.annotation_id}data-annotation-id="{$paragraph.annotation_id}"{/if}>
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
            <!--div class="ner-paragraph-controls">
              <button class="btn btn-success ner-btn-finish pull-right" data-par-id="{$paragraph.id}">Закончить разметку абзаца</button>
            </div-->
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
        <div class="span4 ner-table-wrap {if $paragraph.annotation_id}ner-mine{/if}">
          <div class="tabbable dragged-upper">
            <ul class="nav nav-tabs small-tabs">
              <li class="active"><a href="#tab-entities-{$paragraph.id}-mine" data-toggle="tab">Спаны</a></li>
              <li><a href="#tab-mentions-{$paragraph.id}-mine" data-toggle="tab" class="tab-opener">Упоминания</a></li>
              <li><a href="#" class="modal-opener" data-toggle="modal" data-target="#objects-modal">Объекты</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab-entities-{$paragraph.id}-mine">
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
                    {if $paragraph.annotation_id}
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

              <div class="tab-pane" id="tab-mentions-{$paragraph.id}-mine">
              <table class="table moderator-mentions mentions-table table-condensed" data-par-id="{$paragraph.id}">
              {foreach $paragraph.mentions as $id => $mention}
                {if $id == 0}{continue}{/if}
                <tr data-mention-id="{$id}" data-object-id="{$mention.object_id}" {if $mention.object_id}class="is-in-object"{/if}>
                  <td class="ner-mention-actions"><i class="icon icon-remove ner-remove remove-mention" data-mention-id={$id}></i></td>
                  <td class="ner-mention-text span4">
                    {foreach $mention['entities'] as $ne}
                      [{foreach $ne.tokens as $i => $token}{$token[1]}{if $i
                      < count($ne.tokens)-1} {/if}{/foreach}]
                    {/foreach}
                  </td>
                  <td class="ner-mention-type span3">
                  {if $paragraph.annotation_id}
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
    </div> <!-- tab pane, first -->

    {foreach $paragraph.all_annotations as $uid => $his_annotation}
    <div class="tab-pane" id="annotation-layer-{$uid}-{$paragraph.id}">
      {include file="ner/_partials/paragraph-inner-locked.tpl" paragraph=$paragraph his_paragraph=$his_annotation his_user_id=$uid}
    </div>
    {/foreach}

  </div> <!-- tab content -->
</div>