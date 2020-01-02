PayPlans.ready(function($) {

	// Ensure that the submit button is disabled
	$('[data-pp-submit]').attr('disabled', 'disabled');

	$(document).on('click.tos', '[data-tos-link]', function(event) {
		event.stopPropagation();
		event.preventDefault();

		var appId = $(this).data('id');
		var checkbox = $(this).siblings('[data-tos-checkbox]');
		PayPlans.dialog({
			"content": PayPlans.ajax('plugins/tos/show', {"appId" : appId}),
			"bindings": {
				"{closeButton} click": function() {
					checkbox.removeAttr('checked')
						.trigger('change');

					PayPlans.dialog().close();
				},

				"{submitButton} click": function() {

					checkbox.prop('checked', true)
						.trigger('change');

					PayPlans.dialog().close();
				}
			}
		});
	});

	$(document).on('change.tos', '[data-tos-checkbox]', function() {
		// Go through each checkbox and ensure that they are all checked
		var checked = true;

		$('[data-tos-checkbox]').each(function() {
			var isChecked = $(this).is(':checked');

			if (!isChecked) {
				checked = false;
			}
		});

		if (checked) {
			$('[data-pp-submit]').removeAttr('disabled');
			return;
		}

		$('[data-pp-submit]').attr('disabled', 'disabled');
	});
});