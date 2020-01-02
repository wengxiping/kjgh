PayPlans.ready(function($) {

	$('[data-pp-logout]').on('click', function() {
		$('[data-pp-logout-form]').submit();
	});
});