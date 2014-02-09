$.fn.outerHtml = function() {
	return $('<div>').append($(this).clone()).html();
}

$(document).ready(function() {

	$('.anaph-head').each(function() {
		$(this).popover({
			'html': true,
			'title': 'Выберите именную группу',
			'content': compile_content($(this).attr('data-tid')),
			'placement': 'top',
			'trigger': 'manual'
		});
	});

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
			console.log('whoopie');
		}
	})
});