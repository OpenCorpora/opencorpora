var response_test = [{
    "object_id": 145,
    "mentions": [
        {"mention_id": 13, "texts": ["text1", "text2", "text3"], "tag_id": 1},
        {"mention_id": 15, "texts": ["text2-1", "text3-1"], "tag_id": 1}
    ],
    "properties": [
        {"name": "abcd", "value": "abacaba"},
        {"name": "def", "value": "123"}
    ]},
    {
    "object_id": 147,
    "mentions": [
        {"mention_id": 170, "texts": ["aaa1", "aaa2", "aaa3"], "tag_id": 3},
        {"mention_id": 190, "texts": ["aaa2-1", "aaa3-1"], "tag_id": 2},
        {"mention_id": 100, "texts": ["bbb"], "tag_id": 2}
    ],
    "properties": [
        {"name": "abcd", "value": "abacaba"}
    ]},
];

function loadObjects() {
    $.get("./ajax/ner.php", {
        "act": "listObjects",
        "book_id": $("[name=book_id]").val()
    }).done(function(response) {
        var objects = response_test;
        renderObjects(objects);
    });
}

function renderObjects(objects) {
    var $tbody = $(".objects-table > tbody");
    $tbody.find("tr").remove();
    if (objects.length === 0) return $tbody.append($compileStubRow);

    $.map(objects, function(object, i) {
        $tbody.append($compileTableRow(object));
    });
}

function $compileTableRow(object) {
    var tr = $("<tr>").append(
        $compileDeleteCell(object),
        $compileMentionsCell(object),
        $compilePropertiesCell(object.properties)
    );
    return tr;
}

function $compileDeleteCell(object) {
    return $("<td>").append(
        $("<button>").addClass("btn small btn-danger delete-object")
        .attr("data-object-id", object.object_id).text("Удалить")
    );
}

function $compileMentionsCell(object) {
    var cell = $("<td>");
    $.map(object.mentions, function(mention, i) {
        var $span = $("<span>");
        $.map(mention.texts, function(text, j) {
            $span.append("[" + text + "] ");
        });
        $span.append($("<span>").addClass("label label-palette-4").text("Geo"));
        cell.append($span);
        cell.append($("<br>"));
    });
    return cell;
}

function $compilePropertiesCell(properties) {
    return $("<td>");
}

function $compileStubRow() {
    return $("<tr>").append(
        $("<td>").attr("colspan", 100).text("Пустой список"));
}

var hideObjectsPopover = function() {
   $(".o-floating-block").fadeOut(100);
};

var showObjectsPopover = function(x, y) {
   var l = x - $(".o-floating-block").width() / 2;
   var t = y - $(".o-floating-block").height() - 10;
   if (l < 0) l = 3;
   $(".o-floating-block").css("left", l)
                       .css("top", t);
   $(".o-floating-block").fadeIn(100);
};

var clearObjectsHighlight = function() {
   $(".objects-current-selection").removeClass("objects-current-selection");
};

$(document).ready(function() {
    Mousetrap.bind("o o", function() {
        $("#objects-modal").modal("show");
    });

    $("#objects-modal").on("show", loadObjects);

    $(document).on("click", ".moderator-mentions tr[data-mention-id]", function() {
       $(this).toggleClass("objects-current-selection");

       var current = $(".objects-current-selection");

       if (!current.length) {
          hideObjectsPopover();
       }
       else {
          var offset = current.first().offset();
          var X = offset.left + $(this).width() / 2;
          var Y = offset.top - 10;
          showObjectsPopover(X, Y);
       }
    });

    $(".new-object").click(function() {
       var selected = $(".objects-current-selection");

       var selectedIds = selected.mapGetter("data-mention-id");
       $.post("./ajax/ner.php", {
          act: "createObject",
          mentions: selectedIds,
       }, function(response) {
          notify("Объект добавлен.", "success");
          clearObjectsHighlight();
          hideObjectsPopover();
       });
    });

    $(document).on("click", ".delete-object", function() {
        var object_id = $(this).attr("data-object-id");
        $.post("./ajax/ner.php", {
            act: "deleteObject",
            object_id: object_id
        }, loadObjects);
    });
});