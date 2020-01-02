PayPlans.require()
.done(function($) {
	$('[data-modifier-selection]').on('change', function() {
		var wrapper = $(this).parents('[data-plan-footer]');

		// if the modifier radio is not check, check it.
		wrapper.find('[data-modifier-radio]').prop("checked", true);
		wrapper.find('[data-priceset-selection]').prop("checked", false);

		var subscribeButton = $(this).parents('[data-plan-footer]').find('[data-subscribe-button]');
		var value = $(this).val();

		resetLink(subscribeButton, value);
	});

	$('[data-modifier-radio]').on('change', function() {
		$('[data-priceset-selection]').prop("checked", false);

		var subscribeButton = $(this).parents('[data-plan-footer]').find('[data-subscribe-button]');
		var modifiers = $(this).parents('[data-modifier]').find('[data-modifier-selection]');

		resetLink(subscribeButton, modifiers.val());
	});

	var resetLink = function(button, value) {
		
		var defaultLink = button.data('default-link');
		
		if (value.length > 0) {
			var separator = defaultLink.indexOf("?") == -1 ? '?' : '&';
			defaultLink += separator + 'modifier=' + value;
		}

		button.attr("href", defaultLink);
	}
});