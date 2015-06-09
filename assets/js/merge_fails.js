$(document).ready(function() {
    $(".approve-sample").change(function() {
        $.post("ajax/merge_fails.php", {
            act: "approve",
            value: $(this).is(":checked"),
            id: $(this).attr("data-id")
        });
    });

    $(".comment-cell").blur(function() {
        $.post("ajax/merge_fails.php", {
            act: "comment",
            id: $(this).attr("data-id"),
            text: $(this).text()
        });
    });
});
