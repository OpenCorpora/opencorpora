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
      console.log(response);
    });

  });

});
