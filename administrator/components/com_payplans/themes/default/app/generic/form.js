PayPlans.ready(function($) {

	$('[data-app-all-plans]').on('change', function() {
		var checked = $(this).is(':checked');

		if (checked) {
			$('[data-app-selected-plans]').addClass('t-hidden');
			return;
		}

		$('[data-app-selected-plans]').removeClass('t-hidden');
		return;
	});

	$('[data-app-all-plans]').trigger('change');
});