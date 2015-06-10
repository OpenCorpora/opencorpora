$(document).ready(function() {
    $(".approve-sample").change(function(event) {
        var $c = $(event.target).closest('td');
        $.post("ajax/merge_fails.php", {
            act: "approve",
            value: $(this).is(":checked") ? 1 : 0,
            id: $(this).attr("data-id")
        },
        function() {
            $c.addClass('bggreen');
        });
    });

    $(".comment-cell").blur(function(event) {
        var $c = $(event.target).closest('td');
        $.post("ajax/merge_fails.php", {
            act: "comment",
            id: $(this).attr("data-id"),
            text: $(this).text()
        },
        function() {
            $c.addClass('bggreen');
        });
    });
});
