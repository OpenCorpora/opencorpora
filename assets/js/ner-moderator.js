function highlightForeignEntitiesInParagraph(data, $paragraph_node) {
  $paragraph_node.find('.ner-token-border').remove();
  var entities = data.named_entities.sort(function(a, b) {
    return a.tokens.length - b.tokens.length;
  });
  for (i in entities) {
    var entity = entities[i];
    var type = (entity.tags.length > 1 ? 'ner-multiple-types' : 'border-bottom-palette-'
      + ENTITY_TYPES[entity.tags[0][0]]['color']);

    drawForeignBorder($paragraph_node, entity.tokens, type, entity.id);
  }
}

function drawForeignBorder($paragraph_node, tokens, typestr, entityid) {
  var $tokens = $();
  for (i in tokens) {
    if (typeof tokens[i] == "object")
      $tokens = $tokens.add($paragraph_node.find('.ner-token').filterByAttr('data-foreign-tid', tokens[i][0]));
    else
      $tokens = $tokens.add($paragraph_node.find('.ner-token').filterByAttr('data-foreign-tid', tokens[i]));
  }

  var offset_for_border = 0;
  $tokens.each(function() {
    var highest_border_top = 0;
    $(this).find('.ner-token-border').each(function() {
      highest_border_top = Math.max(parseInt($(this).css('top')), highest_border_top);
    });
    offset_for_border =
      Math.max(highest_border_top, offset_for_border);
  });

  $bd = $('<span>').addClass('ner-token-border').addClass(typestr).attr('data-entity-id', entityid);
  $bd.css('top', offset_for_border + 5);

  var last = $tokens.length - 1;
  $tokens.each(function(index) {
    var $bdc = $bd.clone();
    if (index == 0) $bdc.addClass("first-token");
    if (index == last) $bdc.addClass("last-token");
    $(this).find('.ner-token-borders').append($bdc);
  });
}

$(document).ready(function() {

  $("a.upper-tab-nav").on("shown", function(e) {
    var link = $(e.target);
    var par = $(link.attr("href")).find(".ner-paragraph");
    var user_id = $(link).attr("data-user-id");

    if (par.data("already-highlighted")) return;

    for (i in PARAGRAPHS) {
      if (PARAGRAPHS[i].id != par.attr("data-his-par-id")) continue;
      var this_user_annotations = PARAGRAPHS[i]["all_annotations"][user_id];

      highlightForeignEntitiesInParagraph(this_user_annotations, par);
    }

    par.data("already-highlighted", true);
  });

  $(".copy-entity").on("click", function() {
    var btn = $(this);
    var $mod_paragraph = btn.parents(".tabbable").find(".moderator-paragraph-wrap");
    var annot_id = $mod_paragraph.attr("data-annotation-id");

    if (!annot_id) return notify("Модераторская разметка абзаца ещё не открыта.", "warning");
    $.post('/ajax/ner.php', {
      act: 'copyEntity',
      annot_id: annot_id,
      entity_id: btn.attr("data-entity-id")
    }, function(response) {
      var new_entity_id = response.id;
      var par_id = response.paragraph_id;
      var $paragraph = $(".moderator-paragraph-wrap > .ner-paragraph")
        .filterByAttr("data-par-id", par_id);

      var entity_tag_ids = response.tag_ids;
      var entity_text = $.map(response.tokens_info, function(t) {
        return t[1];
      }).join(" ");

      var token_ids = $.map(response.tokens_info, function(t) {
        return t[0];
      });

      // there are many ner-table's, but only one with data-par-id (mine)
      var t = $('table.ner-table').filterByAttr('data-par-id', par_id);

      var typestr;
      if (entity_tag_ids.length == 1) {
          typestr = 'border-bottom-palette-' + ENTITY_TYPES[entity_tag_ids[0]]['color'];
      } else {
          typestr = 'ner-multiple-types';
      }

      $.each(PARAGRAPHS, function(i, par) {
        if (par.id != par_id) return;
        PARAGRAPHS[i].named_entities.push({
          tokens: token_ids,
          tags: $.map(entity_tag_ids, function(n) { return [[n, ENTITY_TYPES[n]['name']]]; }),
          id: new_entity_id
        });
        highlightEntitiesInParagraph(PARAGRAPHS[i], $paragraph);
      });

      var tr = $('.templates').find('.tr-template').clone().removeClass('tr-template');
      tr.add(tr.find('.remove-entity')).add(tr.find('.selectpicker-tpl')).attr('data-entity-id', response.id);
      tr.find('.selectpicker-tpl').find('option').each(function(i, o) {
          if (entity_tag_ids.indexOf(parseInt($(o).text())) != -1) $(o).attr('selected', true);
      });

      tr.find('.selectpicker-tpl').removeClass('selectpicker-tpl').addClass('selectpicker').selectpicker();
      tr.find('.ner-entity-text-wrap').text(entity_text);
      t.append(tr);
    });

  });

});
