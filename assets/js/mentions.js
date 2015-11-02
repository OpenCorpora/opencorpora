$(document).ready(function() {

	$(document).on("click", ".ner-table tr[data-entity-id]", function() {
		$(this).toggleClass("mentions-current-selection");
	});
});