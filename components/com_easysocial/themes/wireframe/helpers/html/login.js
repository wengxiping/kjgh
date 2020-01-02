
EasySocial
.require()
.script('shared/fields/base', 'shared/fields/validate')
.done(function($) {

	// Implement the controller on the fields
	$('[data-registermini-fields-item]').addController(EasySocial.Controller.Field.Base, {
		"userid": 0,
		"mode": "registermini"
	});

	$('[data-registermini-submit]').on('click', function() {
		var button = $(this);

		if (button.enabled()) {
			// Disable the button to prevent multiple clicks
			button.disabled(true);

			$('[data-registermini-form]')
				.validate({
					"mode": "onRegisterMini"
				}).done(function() {
					button.enabled(true);

					$('[data-registermini-form]').submit();
				}).fail(function() {
					button.enabled(true);
				});
		}
	});
});
