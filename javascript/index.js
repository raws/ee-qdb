$("#quote-filters-toggle").click(function() {
	$("#quote-filters").slideToggle();
	return false;
});

$("#clear-filters").click(function() {
	window.location = $("#filter-form").attr("action");
});

$(".toggle_all").toggle(function() {
	$("input.toggle").each(function() { this.checked = true; });
}, function() {
	$("input.toggle").each(function() { this.checked = false; });
})
