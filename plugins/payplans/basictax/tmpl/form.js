PayPlans.ready(function($) {

	$('[data-pp-basictax-country]').on('change', function() {
		var country = $('[data-pp-basictax-country]').val();
		var invoiceKey = $('[data-pp-invoice-key]').val();

		$('[data-pp-basictax-message]').hide();

		PayPlans.ajax('site/controllers/app/trigger', {
			"event": "onPayplansTaxRequest",
			"event_args": {
				"invoice_key": invoiceKey,
				"country": country
			}
		}).done(function(html, total){

			// Reload the page to show the updated invoice
			// window.location.reload();

			// remove all
			$('[data-pp-modifier-discount]').remove();

			// now repopulate with the udpates
			$('[data-pp-modifiers]').prepend(html);
			$('[data-pp-payable-label]').html(total);


		}).fail(function(message) {
			$('[data-pp-basictax-message]').show();

			$('[data-pp-basictax-message]').html(message);
		});

		return;
	});

	$('[data-pp-checkout-form]').on('submit', function(ev) {

		// check if country selected or not.
		if ($('[data-pp-basictax-country]').val() == '0') {
			var errorMsg = "<?php echo JText::_('COM_PP_APP_BASICTAX_PLEASE_SELECT_COUNTRY', true); ?>";

			$('[data-pp-basictax-message]').html(errorMsg);
			$('[data-pp-basictax-message]').show();


			// focus on country selection
			$('[data-pp-basictax-country]').focus();

			return false;
		}
	});

});
