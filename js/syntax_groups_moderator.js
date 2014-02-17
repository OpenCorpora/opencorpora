
// Переопределяем функцию из syntax_groups.js
function group_tokens() {

    for (uid in groups_json) {

        for (i in groups_json[uid]['simple']) {
            base = $();
            group = groups_json[uid]['simple'][i];

            for (j in group.tokens) {
                base = base.add('div[data-userid=' + uid + '] > span.token[data-tid=' + group.tokens[j] + ']');
            }
            base.wrapAll('<span class="group" data-gid="' + group.id + '"></span>');
        }

        for (i in groups_json[uid]['complex']) {
            base = $();
            group = groups_json[uid]['complex'][i];

            for (j in group.children) {
                base = base.add('div[data-userid=' + uid + '] > span.group[data-gid=' + group.children[j] + ']');
                $('div[data-userid=' + uid + '] > span.group[data-gid=' + group.children[j] + ']').addClass('deep');
            }
            base.wrapAll('<span class="group" data-gid="' + group.id + '"></span>');
        }

    }
}

function show_copied_groups(groups) {
    b = $('#my_syntax .tokens');

    for (i in groups['simple']) {
        base = $();
        group = groups['simple'][i];

        for (j in group.tokens) {
            base = base.add(b.children('span.token[data-tid=' + group.tokens[j] + ']'));
        }

        base.wrapAll('<span class="group" data-gid="' + group.id + '"></span>');
    }

    for (i in groups['complex']) {
        base = $();
        group = groups['complex'][i];

        for (j in group.children) {
            base = base.add(b.children('span.group[data-gid=' + group.children[j] + ']'));
            $(b.children('span.group[data-gid=' + group.children[j] + ']')).addClass('deep');
        }
        base.wrapAll('<span class="group" data-gid="' + group.id + '"></span>');
    }
}

function copy_group(table_row) {
    $.post('/ajax/syntax_group.php', {
        act: 'copyGroup',
        gid: table_row.attr('data-gid'),
        sentence_id: table_row.parents('.table_wrapper').attr('data-sentenceid')
    }, function(response) {
        if (!response.error) {
            refresh_table();
            show_copied_groups(response.new_groups);
            notify("Группа скопирована.");
        } else {
            notify('Произошла ошибка. Напишите разработчикам \
             или создайте тикет в http://code.google.com/p/opencorpora')
            console.log(response);
        }
    }, 'json');
}

$(document).ready(function() {
    $('.syntax_groups').on('click', '.copy_group', function() {

        // Если пользователь действительно этого хочет,
        if (window.confirm("Вы уверены, что хотите скопировать себе именную группу «" +
          $(this).parents('tr').find('td.group_text').text() + "»?")) {
            copy_group($(this).parents('tr'));
        }

    });
});
