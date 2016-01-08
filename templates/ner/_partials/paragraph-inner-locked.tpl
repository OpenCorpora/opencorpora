<div class="row ner-row
ner-row-disabled">
<div class="span8">
  <div class="ner-paragraph-wrap ner-disabled">
    <p class="ner-paragraph" data-his-par-id="{$paragraph.id}">
    {foreach name=s item=sentence from=$paragraph.sentences}
      {foreach name=t item=token from=$sentence.tokens}{capture name="token"}
        <span
          id="t{$token.id}-not-mine"
          data-foreign-tid="{$token.id}"
          class="ner-token">
          <span class="ner-token-text">{$token.text|htmlspecialchars}</span>
          <span class="ner-token-borders"></span>
        </span>{/capture}{$smarty.capture.token|strip:" "}{/foreach}
    {/foreach}
    </p>
  </div>
</div>
<div class="span4 ner-table-wrap">
  <div class="tabbable dragged-upper">
    <ul class="nav nav-tabs small-tabs">
      <li class="active"><a href="#tab-entities-{$his_user_id}-{$paragraph.id}" data-toggle="tab">Спаны ({count($his_paragraph.named_entities)})</a></li>
      <li><a href="#tab-mentions-{$his_user_id}-{$paragraph.id}" data-toggle="tab" class="tab-opener">Упоминания ({count($his_paragraph.mentions)})</a></li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active" id="tab-entities-{$his_user_id}-{$paragraph.id}">
        <table class="table ner-table table-condensed">
        {foreach $his_paragraph.named_entities as $ne}
          <tr data-entity-id="{$ne.id}">
            <td class="ner-entity-actions"><i class="icon icon-magnet copy-entity" data-entity-id={$ne.id}></i></td>

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
              {foreach $ne.tags as $tag}
                <span class="label label-palette-{$entity_types[$tag[0]].color}">{$tag[1]}</span>
              {/foreach}
            </td>
          </tr>
        {/foreach}
        </table>
      </div>

      <div class="tab-pane" id="tab-mentions-{$his_user_id}-{$paragraph.id}">
      <table class="table mentions-table table-condensed">
      {foreach $his_paragraph.mentions as $id => $mention}
        {if $id == 0}{continue}{/if}
        <tr data-mention-id="{$id}">
          <td class="ner-mention-actions"><i class="icon icon-magnet copy-mention" data-mention-id={$id}></i></td>
          <td class="ner-mention-text span4">
            {foreach $mention['entities'] as $ne}
              [{foreach $ne.tokens as $i => $token}{$token[1]}{if $i
              < count($ne.tokens)-1} {/if}{/foreach}]
            {/foreach}
          </td>
          <td class="ner-mention-type span3">
            {foreach $mention_types as $type}
              {if $type['id'] == $mention['type']}
              <span class="label label-palette-{$type['color']}">{$type['name']}</span>
              {/if}
            {/foreach}
          </td>
        </tr>
      {/foreach}
      </table>
      </div>
    </div>
    <div class="buttons-wrap">
      <button class="btn btn-mini copy-all-entities" data-his-annot-id="{$paragraph.annotation_id}">
        <i class="icon icon-magnet"></i> Все спаны</button>
      <button class="btn btn-mini copy-all" data-his-annot-id="{$paragraph.annotation_id}">
        <i class="icon icon-magnet"></i> Все спаны +  упоминания</button>
    </div>
  </div>
</div>
</div>