PayPlans.ready(function($) {

	var isSubmitting = false;

	$('[data-pp-authorizecim-submit]').on('click', function() {
		
		if (isSubmitting) {
			return;
		}

		// current button
		var button = $(this);

		// lock the submit button
		isSubmitting = true;

		// Ensure that the submit button is disabled
		$('[data-pp-authorizecim-submit]').attr('disabled', 'disabled');

		// show loading button
		button.addClass('is-loading');

		var form = $('[data-authorize-form]');

		form.submit();

	});
});