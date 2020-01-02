PayPlans.ready(function($) {

	$('[data-toggle]').on('click', function() {
		var tab = $(this);
		var id = tab.data('id');

		$('[data-pp-active-tab]').val(id);
	});
});