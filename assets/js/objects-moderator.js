/*var response_test = [{
    "object_id": 145,
    "mentions": [
        {"mention_id": 13, "text": "[text1] [ext2 text3]", "object_type_id": 1},
        {"mention_id": 15, "text": "[text2-1] [text3-1]", "object_type_id": 1}
    ],
    "properties": [
        ["abcd", "abacaba"],
        ["def", ""]
    ]},
    {
    "object_id": 147,
    "mentions": [
        {"mention_id": 170, "text": "[aaa1] [aaa2] [aaa3]", "object_type_id": 3},
        {"mention_id": 190, "text": "[aaa2-1] [aaa3-1]", "object_type_id": 2},
        {"mention_id": 100, "text": "[bbb]", "object_type_id": 2}
    ],
    "properties": [
        ["abcd", "abba"],
        ["def", ""]
    ]},
];*/

var OBJECT_PROPS = [];

function loadObjects() {
    $.post("./ajax/ner.php", {
        "act": "getObjects",
        "book_id": $("[name=book_id]").val()
    }).done(function(response) {
        var objects = response.objects; // response_test;
        OBJECT_PROPS = response.possible_props;
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
    var tr = $("<tr>").attr("data-object-id", object.object_id).append(
        $compileIdCell(object),
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

function $compileIdCell(object) {
    return $("<td>").text(object.object_id);
}

function $compileMentionsCell(object) {
    var cell = $("<td>");
    $.map(object.mentions, function(mention, i) {
        var $span = $("<span>");
        $span.append(
            $("<span>").addClass("label label-inverse unlink-mention")
                .text("Удалить")
                .attr("data-mention-id", mention.mention_id));

        $span.append(" [" + mention.text + "] ");
        $span.append($("<span>").addClass(
            "label label-palette-" + MENTION_TYPES[mention.object_type_id]['color'])
            .text(MENTION_TYPES[mention.object_type_id]['name']));

        cell.append($span);
        cell.append($("<br>"));
    });
    return cell;
}

function $makeInput(name, value, val_id) {
    return $("<div>").addClass("input-prepend input-append").append(
        $("<span>").addClass("add-on").text(name),
        $("<input>").addClass("span4 object-property-input")
            .attr("type", "text")
            .attr("list", "spans-datalist")
            .attr("data-val-id", val_id)
            .attr("data-initial-value", value)
            .val(value),
        $("<span>").addClass("add-on delete-prop")
            .attr("data-val-id", val_id).html("&times;")
    );
}

function $makeNewPropInput() {
    var $select = $("<select>").addClass("new-prop-select span3 inline");

    $.map(OBJECT_PROPS, function(prop) {
        $select.append($("<option>").attr("value", prop[0])
            .text(prop[1]));
    });

    return $("<div>").addClass("input-append inline").append(
        $select,
        $("<span>").addClass("add-on add-prop").html("&plus;")
    );
}

function $compilePropertiesCell(properties) {
    var cell = $("<td>");
    $.map(properties, function(property, val_id) {
        cell.append($makeInput(property[1], property[2], val_id));
    });
    cell.append($makeNewPropInput());
    return cell;
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
        // if ($(this).attr("data-object-id") !== "0") return;

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

    $(".mentions-tab-opener").dblclick(function(e) {
        e.preventDefault();
        $(".mentions-tab-opener").tab("show");
    });

    $(".new-object").click(function() {
        var selected = $(".objects-current-selection");
        var selectedIds = selected.mapGetter("data-mention-id");
        $.post("./ajax/ner.php", {
            act: "createObject",
            mentions: selectedIds,
        }, function(response) {
            notify("Объект добавлен.", "success");

            $.map(selected, function(tr) {
                $(tr).attr("data-object-id", response.object_id);
                $(tr).addClass("is-in-object");
            });

            clearObjectsHighlight();
            hideObjectsPopover();
         });
    });

    $(".add-to-object").click(function() {
        var selected = $(".objects-current-selection");
        var selectedIds = selected.mapGetter("data-mention-id");

        var object_id = $(".add-to-object-id").val();
        if (!object_id) return false;

        $.map(selectedIds, function(id) {
            $.post("./ajax/ner.php", {
                act: "linkMentionToObject",
                mention_id: id,
                object_id: object_id,
            });
        });

        $.map(selected, function(tr) {
            $(tr).attr("data-object-id", object_id);
            $(tr).addClass("is-in-object");
        });

        clearObjectsHighlight();
        hideObjectsPopover();
    });

    $(document).on("click", ".delete-object", function() {
        var object_id = $(this).attr("data-object-id");
        $.post("./ajax/ner.php", {
            act: "deleteObject",
            object_id: object_id
        }, loadObjects);
    });

    $(document).on("click", ".unlink-mention", function() {
        var mention_id = $(this).attr("data-mention-id");
        $.post("./ajax/ner.php", {
            act: "deleteMentionFromObject",
            mention_id: mention_id
        }, loadObjects);
    });

    $(document).on("blur", ".object-property-input", function() {
        var input = $(this);
        if (input.val() == input.attr("data-initial-value")) return;
        $.post("./ajax/ner.php", {
            act: "updateObjectProperty",
            val_id: input.attr("data-val-id"),
            prop_value: input.val(),
            object_id: input.parents("tr").attr("data-object-id")
        }); //, loadObjects);
    });

    $(document).on("click", ".add-prop", function() {
        var el = $(this);
        var tr = el.parents("tr");
        $.post("./ajax/ner.php", {
            act: "addObjectProperty",
            prop_id: el.parent().find(".new-prop-select").val(),
            object_id: tr.attr("data-object-id")
        }, loadObjects);
    });

    $(document).on("click", ".delete-prop", function() {
        var el = $(this);
        var tr = el.parents("tr");
        $.post("./ajax/ner.php", {
            act: "deleteProperty",
            val_id: el.attr("data-val-id")
        }, loadObjects);
    });

});