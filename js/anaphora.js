$.fn.outerHtml = function() {
	return $('<div>').append($(this).clone()).html();
}

$(document).ready(function() {

	$('.anaph-head').each(function() {
		$(this).popover({
			'html': true,
			'title': 'Выберите именную группу',
			'content': compile_content($(this).attr('data-tid')),
			'placement': function (tip, element) {
				  var offset = $(element).offset();
				  height = $(document).outerHeight();
				  width = $(document).outerWidth();
				  if (offset.left < 200) placement = 'right';
				  else placement = 'top';
				  return placement;
		   },
			'trigger': 'manual',
			'template': '<div class="popover popover-wide"> \
				<div class="arrow"></div><div class="popover-inner"> \
					<h3 class="popover-title"></h3> \
					<div class="popover-content"><p></p></div> \
					</div></div>'
		});
	});


	// Уведомление в уголке
	function notify(text) {
	    $('.notifications').notify({
	        message: {
	            text: text
	        },
	        type: 'info'
	    }).show();
	}

	// Создает HTML всплывающего окна над токеном
	function compile_content(token_id) {
		content = $('<ol>');
		if (token_id in syntax_groups_json) {
			groups = syntax_groups_json[token_id];

			if (groups.simple.length > 0) {
				for (i in groups.simple) {
					g = groups.simple[i];
					$('<li>').html(
						'<a class="link-anaphora" href="#" data-gtype="' +
						g.type + '" data-gid="' + g.id + '">' +
						g.text + '</a> - <i>' + group_types[g.type] + '</i>'

					).appendTo(content);
				}
			}

			if (groups.complex.length > 0) {
				for (i in groups.complex) {
					g = groups.complex[i];
					$('<li>').html(
						'<a class="link-anaphora" href="#" data-gtype="' +
						g.type + '" data-gid="' + g.id + '">' +
						g.text + '</a> - <i>' + group_types[g.type] + '</i>'

					).appendTo(content);
				}
			}
		}
		return content.outerHtml();
	}

	$('.anaph-prop').click(function(e) {
		$('.anaph-prop').removeClass('anaph-active');
		$(this).addClass('anaph-active');

		$('.anaph-head').removeClass('anaph-active');

		$(this).parent().prevAll().find('.anaph-head').addClass('anaph-active');
		$(this).prevAll('.anaph-head').addClass('anaph-active');
	});

	$(document).on('click', '.anaph-head.anaph-active', function(e) {
		$('.anaph-head').not($(this)).popover('hide');
		$(this).popover('toggle');
	});

	$(document).on('click', '.link-anaphora', function(e) {
		e.preventDefault();

		if (window.confirm('Создать связь между «' +
			$('.anaph-prop.anaph-active').text() +
			'» и именной группой «' +
			$(this).text() +
			' (тип ' + group_types[$(this).attr('data-gtype')] + ')» ?'
			)) {

			gr = $(this);

			$.post('ajax/anaphora.php', {
				'act': 'new',
				'anph_id': $('.anaph-prop.anaph-active').attr('data-tid'),
				'group_id': gr.attr('data-gid')
			}, function(response) {
				$('.anaph-head').popover('hide');

				if (response.error) {
					notify('Произошла ошибка.');
				} else {
					notify('Связь сохранена.');

					$('.anaph-table').find('.tr-stub').hide();

					tr = $('.anaph-table .tr-tpl').clone();
					tr.find('.remove-anaphora').attr('data-aid', response.aid);
					tr.find('.anaph-text')
						.text($('.anaph-prop.anaph-active').text())
						.attr('data-tid', $('.anaph-prop.anaph-active').attr('data-tid'));

					tr.find('.group-text').text(gr.text())
						.attr('data-gid', gr.attr('data-gid'))
						.attr('data-tokens', response.token_ids);

					tr.removeClass('tr-tpl');
					$('.anaph-active').removeClass('anaph-active');
					$('.anaph-table tr:last').after(tr);
				}
			}, 'json');
		}
	});

	$(document).on('click', '.remove-anaphora', function(e) {
		tr = $(this).parents('tr');
		anaph = tr.find('td.anaph-text');
		group = tr.find('td.group-text');

		if (window.confirm('Вы хотите удалить связь «' + anaph.text() + '» -> «' +
			group.text() + '» ?')) {

			$.post('ajax/anaphora.php', {
				'act': 'delete',
				'aid': $(this).attr('data-aid')
			}, function(response) {
				if (response.error)
					return notify('Произошла ошибка.');

				tr.remove();
				notify('Анафора удалена.');

				if ($('.anaph-table > tr').length <= 2) $('.tr-stub').show();
			});
		}

	});

	var animation_lock = false;
	$(document).on('click', '.anaph-table td:not(.actions)', function() {
		if (animation_lock) return;
		animation_lock = true;
		tr = $(this).parent();

		anaph_id = tr.find('td.anaph-text').attr('data-tid');
		group_id = tr.find('td.group-text').attr('data-gid');
		$('html, body').animate({scrollTop:$('#t' + anaph_id).position().top - 50}, 'slow');

		group_tokens = JSON.parse(tr.find('td.group-text').attr('data-tokens'));
		$.merge(group_tokens, [anaph_id]);
		group_tokens.forEach(function(id) {

			el = $('#t' + id);
			color = el.css('color');
			bg = el.css('background-color');

			$('#t' + id).animate({
				'color': 'white',
				'background-color': '#2ECC40'
			}, 500).delay(3000).animate({
				'color': color,
				'background-color': bg
			}, 500, function() {
				animation_lock = false;
			});
		});

	});
});
