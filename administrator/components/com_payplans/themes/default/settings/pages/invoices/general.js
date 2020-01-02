PayPlans.ready(function($) {

	$('[data-remove-image]').on('click', function() {

		$.Joomla('submitform', ['config.removeLogo']);
	});
});