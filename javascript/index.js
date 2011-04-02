$(".toggle_all").toggle(function() {
	$("input.toggle").each(function() { this.checked = true; });
}, function() {
	$("input.toggle").each(function() { this.checked = false; });
})
