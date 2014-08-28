$(document).ready(function() {
    // comments
    $('.comment-marker').popover({
        position: 'right',
        html: true,
        title: 'Комментарии к абзацу',
        trigger: 'manual',
        content: function() {
            stub = $('.templates > .comment-add-stub').clone();
            stub.find('button').attr('data-paragraph-id',
            $(this).attr('data-paragraph-id'));

            return $('.comment-list-stub')
                .filterByAttr('data-paragraph-id', $(this).attr('data-paragraph-id')).html() + stub.html();
        },

        'template': '<div class="popover popover-stretch comment-popover"> \
            <div class="arrow"></div><div class="popover-inner"> \
            <h3 class="popover-title"></h3> \
            <div class="popover-content"><p></p></div> \
            </div></div>'
    });

    $(document).on('click', 'button.btn-comment-add', function(e) {
        e.preventDefault();
        btn = $(this),
        textarea = $(this).siblings('textarea');
        comment = textarea.val();
        if (!comment) return;

        $.post('/ajax/ner.php', {
            act: 'addComment',
            paragraph: btn.attr('data-paragraph-id'),
            comment: comment
        },
        function(response) {
            if (response.error) return notify('Произошла ошибка при сохранении.', 'error');
            notify('Комментарий сохранен.');
            cm = $('.comment-marker')
                .filterByAttr('data-paragraph-id', btn.attr('data-paragraph-id'));
            cm.popover('hide');
            inc_count = cm.text() == '+' ? '1' : parseInt(cm.text()) + 1;
            cm.find('span').text(inc_count);

            $('.comment-list-stub').filterByAttr('data-paragraph-id',
                btn.attr('data-paragraph-id')).append(
                $('<div>').addClass('comment-wrap').append(
                    $('<div>').addClass('comment-text').text(comment),
                    $('<div>').addClass('comment-date').text(response.time)
                )
            );
        });
    });

    $('.comment-marker').click(function(e) {
        $('.comment-marker').not($(this)).popover('hide');
        $(this).popover('show');
    });

    $('body').on('click', function(e) {
        if ($(e.target).parents('.popover.in').length === 0 &&
            $(e.target).parents('.comment-marker').length === 0) {
            $('.comment-marker').popover('hide');
        }
    });

}); // document.ready