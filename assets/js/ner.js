// from syntax_groups.js
function check_adjacency($token) {
   var $p = $token.prev();
   if ($p.length && $p.hasClass('ner-token-selected'))
      return true;
   $p = $token.next();
   if ($p.length && $p.hasClass('ner-token-selected'))
      return true;
   return false;
}

function is_uttermost($target) {
   var selected = $target.parent().find('.ner-token-selected');
   var i = selected.index($target);
   return (i == 0 || i == (selected.length-1));
}

function click_handler($target) {
   if (!$target.hasClass('ner-token-selected')) {
     if (!check_adjacency($target)) {
         $target.parent().find('.ner-token-selected').removeClass('ner-token-selected');
     }
     $target.addClass('ner-token-selected');
   }
   else {
     if (is_uttermost($target)) $target.removeClass('ner-token-selected');
   }
}

// end from syntax_groups.js

var miscTypeId = 6;

var clearHighlight = function() {
    $(document).find('.ner-token-selected').removeClass('ner-token-selected');
};

var clearSelectedTypes = function() {
    $('.type-selector').find('.btn').removeClass('active');
};

var hideTypeSelector = function() {
   $('.floating-block').fadeOut(100, deactivateHotKeys);
};

var showTypeSelector = function(x, y) {
   var l = x - $('.floating-block').width() / 2;
   var t = y - $('.floating-block').height() - 30;
   if (l < 0) l = 3;
   $('.floating-block').css('left', l)
                       .css('top', t);

   $('.floating-block').fadeIn(100, activateHotKeys);
};

var activateHotKeys = function() {
   // disabled for now because some types start with the same letter (= have the same key)
   return false;
   $('.type-selector > .btn').each(function(i, btn) {
      var btn = $(btn);
      Mousetrap.bind([btn.attr('data-hotkey') + ' ' + btn.attr('data-hotkey'),
                      'alt+' + btn.attr('data-hotkey')], function() {

         $('body').append(d = $("<div>").addClass("kbd-visual").text(btn.text()));
         d.fadeIn(100).delay(1000).fadeOut(100, function() { d.remove(); });
         btn.click();
      });
   });
};

var deactivateHotKeys = function() {
   $('.type-selector > .btn').each(function(i, btn) {
      var btn = $(btn);
      Mousetrap.unbind(btn.attr('data-hotkey') + ' ' + btn.attr('data-hotkey'));
      Mousetrap.unbind('alt+' + btn.attr('data-hotkey'));
   });
};

var log_event = function(type, message, id, extra_data_as_string) {
    $.post('/ajax/ner.php', {
        act: 'logEvent',
        type: type,
        id: id,
        event: message,
        data: extra_data_as_string
    });
}

var paragraph__textSelectionHandler = function(e) {
    clearHighlight();
    clearSelectedTypes();

    var sel = rangy.getSelection();
    var range = sel.getRangeAt(0);
    if (range.collapsed) {
        log_event("selection", "text selection in paragraph removed", $(e.target).parents('.ner-paragraph').attr('data-par-id'));
        hideTypeSelector();
        return;
    }

    var nodes = range.getNodes();
    var spans = (nodes.length == 1) ? $(nodes[0]).parents('.ner-token') : $(nodes).filter('span.ner-token');
    spans.addClass('ner-token-selected');
    var offset = spans.last().offset();
    var X = offset.left + $(spans.last()).width() / 2;
    var Y = offset.top;
    showTypeSelector(X, Y);
    log_event("selection", "text selection in paragraph", $(e.target).parents('.ner-paragraph').attr('data-par-id'), spans.text());
    sel.removeAllRanges();
};

var token__clickHandler = function(e) {
    var in_other = $('.ner-paragraph').not($(this).parent()).find('.ner-token-selected');
    if (in_other.length > 0) {
        log_event("selection", "removed selection by clicking in another paragraph", $(e.target).parents('.ner-paragraph').attr('data-par-id'));
        in_other.removeClass('ner-token-selected');
        clearSelectedTypes();
    }

    click_handler($(this));

    if ($('.ner-token-selected').length == 0) {
        log_event("selection", "removed selection", $(e.target).parents('.ner-paragraph').attr('data-par-id'));
        hideTypeSelector();
        clearSelectedTypes();
    } else {
        log_event("selection", "selection updated by clicking", $(e.target).parents('.ner-paragraph').attr('data-par-id'),
            $('.ner-token-selected').text());
        var offset = $(e.target).offset();
        var X = offset.left + $(e.target).width() / 2;
        var Y = offset.top;
        showTypeSelector(X, Y);
    }
};

function highlightEntitiesInParagraph(data, $paragraph_node) {
  $paragraph_node.find('.ner-token-border').remove();
  var entities = data.named_entities.sort(function(a, b) {
    return a.tokens.length - b.tokens.length;
  });
  for (i in entities) {
    var entity = entities[i];
    var type = (entity.tags.length > 1 ? 'ner-multiple-types' : 'border-bottom-palette-'
      + ENTITY_TYPES[entity.tags[0][0]]['color']);

    drawBorder(entity.tokens, type, entity.id);
  }
}

function drawBorder(tokens, typestr, entityid) {
  var $tokens = $();
  for (i in tokens) {
    if (typeof tokens[i] == "object")
      $tokens = $tokens.add($('.ner-token').filterByAttr('data-tid', tokens[i][0]));
    else
      $tokens = $tokens.add($('.ner-token').filterByAttr('data-tid', tokens[i]));
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
    $.fn.mapGetter = function(prop) {
        return $(this).map(function(i, e) {
            return $(e).attr(prop);
        }).get();
    };

    $.fn.filterByAttr = function(attr, val) {
        return $(this).filter(function(i, e) {
            return $(e).attr(attr) == val;
        });
    };

    var originalAddClassMethod = $.fn.addClass;
    var originalRemoveClassMethod = $.fn.removeClass;

    $.fn.addClass = function() {
        var result = originalAddClassMethod.apply(this, arguments);
        $(this).trigger('cssClassAdded', arguments);
        return result;
    }

    $.fn.removeClass = function() {
        var result = originalRemoveClassMethod.apply(this, arguments);
        $(this).trigger('cssClassRemoved', arguments);
        return result;
    }

    // $(el).syncByClass(other-el)
    // when el gets new classes, other-el gets them too
    $.fn.syncByClass = function(that) {
        $(this).on('cssClassAdded', function(e, className) {
            if ($(e.target).is(this)) $(that).addClass(className);

        });
        $(this).on('cssClassRemoved', function(e, className) {
            if ($(e.target).is(this)) $(that).removeClass(className);
        });
    };

    $.fn.removeClassRegex = function(regex) {
      return $(this).removeClass(function(index, classes) {
        return classes.split(/\s+/).filter(function(c) {
          return regex.test(c);
        }).join(' ');
      });
    };

    $.fn.selectpicker.defaults = {
      noneSelectedText: '',
      noneResultsText: 'Не найдено совпадений',
      countSelectedText: 'Выбрано {0} из {1}',
      maxOptionsText: ['Достигнут предел ({n} {var} максимум)', 'Достигнут предел в группе ({n} {var} максимум)', ['items', 'item']],
      multipleSeparator: ', '
    };

});

$(document).ready(function() {

    $('.selectpicker').selectpicker();
    for (i in PARAGRAPHS) {
      highlightEntitiesInParagraph(PARAGRAPHS[i],
        $('.ner-paragraph').filterByAttr('data-par-id', PARAGRAPHS[i].id));
    }

    $('.ner-row').each(function() {
        $(this).find('.ner-paragraph-wrap').syncByClass($(this).find('.ner-table-wrap'));
    });

   $('.ner-paragraph-wrap').not('.ner-mine').not('.ner-disabled').click(function(e) {

      var parwrap = $(this);
      var par = parwrap.find('.ner-paragraph');

      $.post('/ajax/ner.php', {
         act: 'newAnnotation',
         paragraph: par.attr('data-par-id')
      }, function(response) {
         parwrap.addClass('ner-mine').attr('data-annotation-id', response.id);
      });

   });

    $('button.ner-btn-finish').click(function(e) {
        var btn = $(this);
        e.preventDefault();
      e.stopPropagation();

      if ($('.floating-block').is(':visible')) {
         notify("У вас есть несохраненный спан.", 'error');
         return false;
      }

        $.post('/ajax/ner.php', {
            act: 'finishAnnotation',
            paragraph: btn.parents('.ner-paragraph-wrap').attr('data-annotation-id')
        }, function(response) {
            btn.parents('.ner-paragraph-wrap').removeClass('ner-mine').addClass('ner-disabled');
            btn.parents('.ner-row').find('td.ner-entity-type').each(function(index, td) {
                var td = $(td);
                // this is bad
                td.html(td.find('.bootstrap-select').find('.filter-option').html().replace(',', ''));
            });
        });

    });

   $('button.ner-btn-finish-all').click(function(e) {

      if ($('.floating-block').is(':visible')) {
         notify("У вас есть несохраненный спан.", 'error');
         return false;
      }

      $('.ner-paragraph-wrap.ner-mine').each(function() {
         var parwrap = $(this);

         // this block of code suddenly throws errors when put inside $.post callback
         // so we clean up everything here and send the request afterwards
         parwrap.removeClass('ner-mine').addClass('ner-disabled');
         parwrap.parents('.ner-row').find('td.ner-entity-type').each(function(index, td) {
            var td = $(td);
            // this is bad
            td.html(td.find('.bootstrap-select').find('.filter-option').html().replace(',', ''));
         });

         $.post('/ajax/ner.php', {
            act: 'finishAnnotation',
            paragraph: parwrap.attr('data-annotation-id')
         });

      });

   });


    if ($('.ner-mode-fast').hasClass('active'))
        $(document).on('mouseup', '.ner-paragraph-wrap:not(.ner-disabled) > .ner-paragraph', paragraph__textSelectionHandler);
    else
        $(document).on('click', '.ner-paragraph-wrap:not(.ner-disabled) .ner-token:not(.ner-entity)', token__clickHandler)


   $('.ner-mode-basic').click(function() {
      $(document).on('click', '.ner-paragraph-wrap:not(.ner-disabled) .ner-token:not(.ner-entity)', token__clickHandler);
      $(document).off('mouseup', '.ner-paragraph-wrap:not(.ner-disabled) > .ner-paragraph');
      $.post('/ajax/set_option.php', {option: 5, value: 0});
   });

   $('.ner-mode-fast').click(function() {
      $(document).on('mouseup', '.ner-paragraph-wrap:not(.ner-disabled) > .ner-paragraph', paragraph__textSelectionHandler);
      $(document).off('click', '.ner-paragraph-wrap:not(.ner-disabled) .ner-token:not(.ner-entity)');
      $.post('/ajax/set_option.php', {option: 5, value: 1});
   });

    $('.ner-table').on('change', '.selectpicker', function(e) {
        if ($(this).val() == null) {
            $(this).selectpicker('val', miscTypeId);
        }

        var entityId = $(this).parents('tr').attr('data-entity-id');

        if ($(this).val().length > 1) {
            $('.ner-token-border').filterByAttr('data-entity-id', entityId)
                .removeClassRegex(/border-bottom-palette-\d/)
                .addClass('ner-multiple-types');
        }
        else {
            $('.ner-token-border').filterByAttr('data-entity-id', entityId)
                .removeClass('ner-multiple-types')
            .removeClassRegex(/border-bottom-palette-\d/)
                .addClass('border-bottom-palette-' + ENTITY_TYPES[$(this).val()[0]]['color']);
        }

        log_event("entity", "updated types", entityId, $(this).val().toString());
        $.post('/ajax/ner.php', {
            act: 'setTypes',
            entity: entityId,
            types: $(this).val()
        }, function(response) {
            notify("Типы спана сохранены.");
        });
        e.stopPropagation();
    });

    $('.ner-table').on('click', '.remove-ner', function(e) {
        if (window.confirm("Вы действительно хотите удалить этот спан?")) {
            var tr = $(this).parents('tr');
            var par_id = tr.parents('.ner-table').attr('data-par-id');

            var entityId = tr.attr('data-entity-id');
            log_event("entity", "deleting entity", entityId, tr.find('td.ner-entity-text').text().trim());

            $.post('/ajax/ner.php', {
                    act: 'deleteEntity',
                    entity: entityId
                },
                function(response) {
                  if (response.error) {
                    notify("Не получилось удалить - может, спан содержится в упоминании?", "error");
                    return;
                  }
                  notify("Спан удален.");
                  $('.ner-token-border').filterByAttr('data-entity-id', entityId)
                        .remove();
                    tr.remove();

                    $.each(PARAGRAPHS, function(i, par) {
                        if (par.id != par_id) return;
                        $.each(PARAGRAPHS[i].named_entities, function(j, entity) {
                            if (entity.id === entityId) {
                                delete PARAGRAPHS[i].named_entities[j];
                            }
                        });
                    });

            });
        }
        e.stopPropagation();
    });

    $('.ner-table').on('mouseenter', 'tr',
      function() { // hover in
        var tokens = $('.ner-token-border').filterByAttr('data-entity-id', $(this).attr('data-entity-id'))
          .parents('.ner-token');
        tokens.addClass('ner-token-highlighted');
    });

    $('.ner-table').on('mouseleave', 'tr',
      function() { // hover out
        var tokens = $('.ner-token-border').filterByAttr('data-entity-id', $(this).attr('data-entity-id'))
          .parents('.ner-token');
        tokens.removeClass('ner-token-highlighted');
    });

    $('.ner-type-selector > .btn').click(function() {
        var selected = $('.ner-token-selected');
        var paragraph = selected.parents('.ner-paragraph');
        var typesIds = ($(this).hasClass('composite-type') ?
            $(this).attr('data-type-ids').split(',') : [$(this).attr('data-type-id')]);
        var selectedIds = selected.mapGetter('data-tid');

        $.post('/ajax/ner.php', {
            act: 'newEntity',
            tokens: selectedIds,
            types: typesIds,
            paragraph: paragraph.parents('.ner-paragraph-wrap').attr('data-annotation-id')
        }, function(response) {
            var t = $('table.ner-table').filterByAttr('data-par-id', paragraph.attr('data-par-id'));

            var typestr;
            if (typesIds.length == 1) {
                typestr = 'border-bottom-palette-' + ENTITY_TYPES[typesIds[0]]['color'];
            } else {
                typestr = 'ner-multiple-types';
            }

            $.each(PARAGRAPHS, function(i, par) {
              if (par.id != paragraph.attr('data-par-id')) return;
              PARAGRAPHS[i].named_entities.push({
                tokens: selectedIds,
                tags: $.map(typesIds, function(n) { return [n]; }),
                id: response.id
              });
              highlightEntitiesInParagraph(PARAGRAPHS[i], paragraph);
            });

            var tr = $('.templates').find('.tr-template').clone().removeClass('tr-template');
            tr.add(tr.find('.remove-entity')).add(tr.find('.selectpicker-tpl')).attr('data-entity-id', response.id);
            tr.find('.selectpicker-tpl').find('option').each(function(i, o) {
                if (typesIds.indexOf($(o).text()) != -1) $(o).attr('selected', true);
            });

            tr.find('.selectpicker-tpl').removeClass('selectpicker-tpl').addClass('selectpicker').selectpicker();
            tr.find('.ner-entity-text-wrap').text(selected.text());
            t.append(tr);

            clearHighlight();
            clearSelectedTypes();
            hideTypeSelector();
        });

    });


}); // document.ready
