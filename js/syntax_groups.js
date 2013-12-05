// TODO: jquery.ajax onError! Ошибки парсинга серверной выдачи

// Принимает jQuery-обертку над span.token и проверяет,
// есть ли у следующего или предыдущего span.token класс bggreen
function check_adjacency($token) {
    var $p = $token.prev();
    if ($p.length && $p.hasClass('bggreen'))
            return true;
    $p = $token.next();
    if ($p.length && $p.hasClass('bggreen'))
        return true;
    return false;
}

// Проверяет, является ли токен крайним в выделенной группе
function is_uttermost($target) {
    var selected = $target.parent().find('.bggreen');
    var i = selected.index($target);
    return (i == 0 || i == (selected.length-1));
}

// Пересчитывает количество выделенных токенов\именных групп
// и обновляет #selection_info b
function update_selection() {
    var l = $("span.token.bggreen").length; // + $("span.group.bggreen").length;
    $("#selection_info b").html(l);
    if (l > 1)
        $("#selection_info #new_group").add('#add0').show().find('#add1').hide();
    else {
        $("#selection_info #new_group").hide();
        $("#group_type").hide();
    }
}

// Объединяет выделенные токены в группу и присваивает ей id,
// полученный с сервера
function show_new_group(gid) {
    $('span.bggreen:first').before('<span class="group" id="last_group" data-gid="' + gid + '"></span>');
    $('span.bggreen').appendTo($('span#last_group')).removeClass('bggreen');
    $("span#last_group").attr('id', null);
}


// Обновляет таблицу с группами
function refresh_table() {
    table = $('.syntax_groups');

    $.post('/ajax/syntax_group.php', {
        act: 'getGroupsTable',
        sentence_id: $('#tokens').attr('data-sentenceid'),
    }, function(response) {

        if (response.error) {
            notify('Произошла ошибка. Напишите разработчикам \
             или создайте тикет в http://code.google.com/p/opencorpora.')
            return console.log(response);
        }
        $('#groups_table > .table_wrapper').html(response.table);

    }, 'json');
}

// Сохраняет выделенную в текущий момент группу
function save_group(on_success) {

    parts = $('.bggreen').map(function() {
            return $(this).attr('data-tid');
        }).get();

    type = $('#group_type').val();

    $.post('/ajax/syntax_group.php', {
        act: 'newGroup',
        tokens: parts,
        type: type
    }, function(response) {

        // В ответе должно быть:
        // успешно ли сохранили группу (error),
        // id новой группы (gid)

        if (!response.error) {
           show_new_group(response.gid);
           on_success({gid: response.gid, type: type});
        } else {
            notify('Произошла ошибка. Напишите разработчикам \
             или создайте тикет в http://code.google.com/p/opencorpora.')
            console.log(response);
        }
    }, 'json');

}

// Удаляет именную группу
function delete_group(table_row) {
    $.post('/ajax/syntax_group.php', {
        act: 'deleteGroup',
        gid: table_row.attr('data-gid'),
    }, function(response) {
        if (!response.error) {
            text = table_row.find('td.group_text').text();
            refresh_table();
            $('#tokens').find('.group[data-gid=' + table_row.attr('data-gid') + ']').children().unwrap();
            notify("Группа «" + text + "» удалена.");
        } else {
            notify('Произошла ошибка. Напишите разработчикам \
             или создайте тикет в http://code.google.com/p/opencorpora')
            console.log(response);
        }
    }, 'json');
}

// Сохранить тип именной группы
function set_group_type(gid, type, cb) {
    $.post('/ajax/syntax_group.php', {
        act: 'setGroupType',
        gid: gid,
        type: type
    }, function(response) {

        if (!response.error) {
           cb();
        } else {
            notify('Произошла ошибка. Напишите разработчикам \
             или создайте тикет в http://code.google.com/p/opencorpora')
            console.log(response);
        }
    }, 'json');
}

// Сохранить вершину именной группы
function set_group_head(gid, head_id, cb) {
    $.post('/ajax/syntax_group.php', {
        act: 'setGroupHead',
        gid: gid,
        head_id: head_id
    }, function(response) {

        if (!response.error) {
           cb();
        } else {
            notify('Произошла ошибка. Напишите разработчикам \
             или создайте тикет в http://code.google.com/p/opencorpora')
            console.log(response);
        }
    }, 'json');
}

// Уведомление в уголке
function notify(text) {
    $('.notifications').notify({
        message: {
            text: text
        },
        type: 'info'
    }).show();
}

// Обрабатывает клик на .token
// Если мы кликаем на невыделенный токен, то:
//   если он расположен по соседству с выделенными, выделяем и его;
//   иначе мы убираем все выделения и выделяем только этот токен
// Если мы кликаем на выделенный, то:
//   если этот токен расположен на краю группы, снимаем выделение.
function clck_handler($target) {
    if (!$target.hasClass('bggreen')) {
        if (!check_adjacency($target)) {
            $('span.token').removeClass('bggreen');
        }
        $target.addClass('bggreen');
    }
    else {
        if (is_uttermost($target)) $target.removeClass('bggreen');
    }

    update_selection();
}

// Берет из переменной syntax_groups_json именные группы, находит их в текущем предложении
// и оборачивает в span.group
function group_tokens() {
    if (!syntax_groups_json) {
        return;
    }

    sg = syntax_groups_json;
    for (i in sg) {
        base = $();
        for (j in sg[i].tokens) {
            base = base.add('span.token[data-tid=' + sg[i].tokens[j] + ']');
        }
        base.wrapAll('<span class="group" data-gid="' + sg[i].id + '"></span>');
    }

}

$(document).ready(function(){
    // Группируем токены в именные группы
    group_tokens();

    // Сначала скрываем селект с типами групп,
    $('#group_type').hide();

    // по клику на кнопку "Создать группу" - показываем этот селект,
    $('#add0').click(function() {
        $('#group_type').show();

        // и заменяем кнопку на "Создать!".
        $(this).hide();
        $("#add1").show();
    });

    // А по клику на "Создать!"
    $('#add1').click(function() {
        btn = $(this);
        // не даем пользователю кликнуть еще раз,
        btn.attr('disabled', true);

        // сохраняем группу, которую он(а) выделил(а),
        save_group(function(new_group) {
            // уведомляем пользователя,
            notify("Именная группа добавлена!");

            // скрываем селект с типом группы и снова показываем кнопку "Создать группу",
            $("#group_type").hide();
            $("#add0").show();

            // Обновляем таблицу
            refresh_table();

            // и сбрасываем выделение токенов, все возвращается в исходное состояние.
            update_selection();
            btn.attr('disabled', false);
        });
    });

    // Выбираем только непосредственно токены, исключая токены в группах
    $('#tokens > .token').live('click', function() {
        clck_handler($(this));
    });

    // Таблица с именными группами:

    // По клику на крестик - "Удалить группу"
    $('.syntax_groups').find('.remove_group').live('click', function() {

        // Если пользователь действительно этого хочет,
        if (window.confirm("Вы уверены, что хотите удалить именную группу «" +
          $(this).parents('tr').find('td.group_text').text() + "»?")) {
            // Удаляем
            delete_group($(this).parents('tr'));
        }

    });

    $('.group_type_select').live('change', function() {
        that = $(this);
        set_group_type($(this).parents('tr').attr('data-gid'), $(this).val(), function() {
            refresh_table();
            notify("Тип группы изменен на " + that.find('option:selected').text());
        });
    });

    $('.group_head_select').live('change', function() {
        that = $(this);
        set_group_head($(this).parents('tr').attr('data-gid'), $(this).val(), function() {
            refresh_table();
            notify("Вершина группы изменена на " + that.find('option:selected').text());
        });
    });
});