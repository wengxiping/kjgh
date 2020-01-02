PayPlans.ready(function($) {

	$('[data-toggle]').on('click', function() {
		var element = $(this);
		var id = element.data('id');

		if (!id) {
			return;
		}

		$('[data-pp-active-tab-input]').val(id);
	});
});