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

// Назначить токен вершиной группы
function assign_group_root(element) {
    $(element).parent().find('span').not(element).each(function() {
        $(this).removeClass('group_root');
    })
    if (!$(element).hasClass('group_root'))
        $(element).addClass('group_root');
    else
        $(element).removeClass('group_root');
}

// Пересчитывает количество выделенных токенов\именных групп
// и обновляет #selection_info b
function update_selection() {
    var l = $("span.token.bggreen").length; // + $("span.group.bggreen").length;
    $("#selection_info b").html(l);
    if (l > 1)
        $("#selection_info #new_group").show().find('#add1').hide();
    else {
        $("#selection_info #new_group").hide();
        $("#group_type").hide();
    }
}

// Объединяет выделенные токены в группу и присваивает ей id,
// полученный с сервера
function show_new_group(gid) {
    $('span.bggreen:first').before('<span class="group" id="last_group" data-gid="' + gid + '""></span>');
    $('span.bggreen').appendTo($('span#last_group')).removeClass('token').removeClass('bggreen').unbind('click').not(':first').each(function(i, el) {
        $(el).html(' ' + $(el).html());
    });
    $("span#last_group").attr('id', null);
}

// Сохраняет выделенную в текущий момент группу
function save_group(on_success) {

    parts = $('.bggreen').map(function() {
            return $(this).attr('data-tid');
        }).get();

    $.post('/ajax/syntax_group.php', {
        act: 'newGroup',
        tokens: parts,
        type: $('#group_type').val()
    }, function(xmlResponse) {
        // В ответе должно быть:
        // успешно ли сохранили группу (error),
        // id новой группы (gid)

        if (!xmlResponse.error) {
           show_new_group(xmlResponse.gid);
           on_success();
        } else {
            // TODO
        }
    }, 'xml');

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
            $('span.token').removeClass('bggreen group_root');
        }
        $target.addClass('bggreen');
    }
    else {
        if (is_uttermost($target)) $target.removeClass('bggreen group_root');
    }

    update_selection();
}

$(document).ready(function(){
    $('#group_type').hide();
    $('#add0').click(function() {
        $('#group_type').show();
        $(this).hide();
        $("#add1").show();
    });
    $('#add1').click(function() {
        btn = $(this);
        btn.attr('disabled', true);

        save_group(function() {
            $("#group_type").hide();
            $("#add0").show();
            update_selection();
            btn.attr('disabled', false);
        });
    });
    $('span.token').click(function() {
        clck_handler($(this));
    });
    // $('#main_annot_syntax').delegate('span.group', 'click', function() {
    //     clck_handler($(this));
    // });

    $('#main_annot_syntax').delegate('.group > span', 'click', function(e) {
        e.preventDefault();
        $('#save_group_roots').show();
        assign_group_root(this);
    });

    // Сохранить вершины именных групп
    $('#save_group_roots').click(function(e) {
        btn = $(this);
        btn.attr('disabled', true);
        e.preventDefault();

        $('.group').each(function() {

            if (!$(this).find('.group_root').length) return;

            $.post('/ajax/syntax_group.php', {
                act: 'setGroupRoot',
                root_id: $(this).find('.group_root').attr('data-tid'),
                gid: $(this).attr('data-gid')
            }, function(xmlResponse) {
                // В ответе должно быть:
                // успешно ли сохранили вершину (error)

                if (!xmlResponse.error) {

                } else {
                    // TODO
                }

                btn.attr('disabled', false);
            }, 'xml');
        });
    });
});