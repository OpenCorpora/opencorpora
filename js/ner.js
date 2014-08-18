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
var colorStep = 2;

var clearHighlight = function() {
	$(document).find('.ner-token-selected').removeClass('ner-token-selected');
}

var clearSelectedTypes = function() {
	$('.type-selector').find('.btn').removeClass('active');
}

var notify = function(text, t) {
    $('.notifications').notify({
        message: {
            text: text
        },
        type: typeof t == 'undefined' ? 'info' : t,
    }).show();
}

var paragraph__textSelectionHandler = function(e) {
	clearHighlight();
	$('.floating-block').find('.btn').removeClass('active');

	sel = rangy.getSelection();
	range = sel.getRangeAt(0);
	if (range.collapsed) {
		$('.floating-block').removeClass('visible');
		return;
	}

	nodes = range.getNodes();
	spans = (nodes.length == 1) ? $(nodes[0].parentElement) : $(nodes).filter('span');
	if (!spans.hasClass('ner-entity')) {
		spans.addClass('ner-token-selected');
		$('.floating-block').addClass('visible');
	}
	sel.removeAllRanges();
}

var token__clickHandler = function(e) {
	in_other = $('.ner-paragraph').not($(this).parent()).find('.ner-token-selected');
	if (in_other.length > 0) {
		in_other.removeClass('ner-token-selected');
		clearSelectedTypes();
	}

	click_handler($(this));

	if ($('.ner-token-selected').length == 0) {
		$('.floating-block').removeClass('visible');
		$('.floating-block .btn.active').removeClass('active');
	} else {
		$('.floating-block').addClass('visible');
	}
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

	$('.ner-row').each(function() {
		$(this).find('.ner-paragraph-wrap').syncByClass($(this).find('.ner-table-wrap'));
	});


	$('button.ner-btn-start').click(function(e) {
		btn = $(this);
		e.preventDefault();
		$.post('/ajax/ner.php', {
			act: 'newAnnotation',
			paragraph: btn.attr('data-par-id')
		}, function(response) {
			btn.parents('.ner-paragraph-wrap').addClass('ner-mine').attr('data-annotation-id', response.id);
		});

	});

	$('button.ner-btn-finish').click(function(e) {
		btn = $(this);
		e.preventDefault();
		$.post('/ajax/ner.php', {
			act: 'finishAnnotation',
			paragraph: btn.parents('.ner-paragraph-wrap').attr('data-annotation-id')
		}, function(response) {
			btn.parents('.ner-paragraph-wrap').removeClass('ner-mine').addClass('ner-disabled');
			btn.parents('.ner-row').find('td.ner-entity-type').each(function(index, td) {
				td = $(td);
				// this is bad
				td.html(td.find('.bootstrap-select').find('.filter-option').html().replace(',', ''));
			});
		});

	});


	if (getQueryVariable('ne__fast') == '1') {
		$(document).on('mouseup', '.ner-paragraph-wrap.ner-mine > .ner-paragraph', paragraph__textSelectionHandler);
		// todo keybindings
	}
	else
		$(document).on('click', '.ner-paragraph-wrap.ner-mine .ner-token:not(.ner-entity)', token__clickHandler)


	$('.ner-table-wrap').on('change', '.selectpicker', function(e) {
		if ($(this).val() == null) {
			$(this).selectpicker('val', miscTypeId);
		}

		entityId = $(this).parents('tr').attr('data-entity-id');

		if ($(this).val().length > 1) {
			$('.ner-entity').filterByAttr('data-entity-id', entityId)
				.removeClass('border-bottom-palette-* ')
				.addClass('ner-multiple-types');
		}
		else {
			$('.ner-entity').filterByAttr('data-entity-id', entityId)
				.removeClass('border-bottom-palette-* ner-multiple-types')
				.addClass('border-bottom-palette-' + $(this).val()[0] * colorStep);
		}

		$.post('/ajax/ner.php', {
			act: 'setTypes',
			entity: entityId,
			types: $(this).val()
		}, function(response) {
			notify("Типы сущности сохранены.");
		});
	});

	$('.ner-table-wrap').on('click', '.ner-remove', function(e) {
		if (window.confirm("Вы действительно хотите удалить эту сущность?")) {
			tr = $(this).parents('tr');
         entityId = tr.attr('data-entity-id');

         $.post('/ajax/ner.php', {
            act: 'deleteEntity',
            entity: entityId
         }, function(response) {
            notify("Сущность удалена.");
            $('.ner-entity').filterByAttr('data-entity-id', entityId)
               .removeAttr('data-entity-id')
               .removeClass('ner-entity ner-multiple-types border-bottom-palette-*');
               tr.remove();
         });
		}
	});

	$('.type-selector > .btn').click(function() {
		selected = $('.ner-token-selected');
		paragraph = selected.parents('.ner-paragraph');
		typesIds = [$(this).attr('data-type-id')];
		selectedIds = selected.mapGetter('data-tid');

		$.post('/ajax/ner.php', {
			act: 'newEntity',
			tokens: selectedIds,
			types: typesIds,
			paragraph: paragraph.parents('.ner-paragraph-wrap').attr('data-annotation-id')
		}, function(response) {
			t = $('table').filterByAttr('data-par-id', paragraph.attr('data-par-id'));

			selected.addClass('ner-entity').attr('data-entity-id', response.id);

			if (typesIds.length == 1) {
				selected.addClass('border-bottom-palette-' + typesIds[0] * colorStep);
			} else {
				selected.addClass('ner-multiple-types');
			}

         tr = $('.templates').find('.tr-template').clone().removeClass('tr-template');
         tr.add(tr.find('.remove-entity')).add(tr.find('.selectpicker-tpl')).attr('data-entity-id', response.id);
         tr.find('.selectpicker-tpl').find('option').each(function(i, o) {
            if (typesIds.indexOf($(o).text()) != -1) $(o).attr('selected', true);
         });

         tr.find('.selectpicker-tpl').removeClass('selectpicker-tpl').addClass('selectpicker').selectpicker();
         tr.find('td.ner-entity-text').text(selected.text());
         t.append(tr);

			clearHighlight();
			clearSelectedTypes();
			$('.floating-block').removeClass('visible');
		});

	});

});
