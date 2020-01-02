PayPlans.ready(function($) {

	$('[data-pp-basictax-country]').on('change', function() {
		var country = $('[data-pp-basictax-country]').val();
		var invoiceKey = $('[data-pp-invoice-key]').val();

		PayPlans.ajax('site/controllers/app/trigger', {
			"event": "onPayplansTaxRequest",
			"event_args": {
				"invoice_key": invoiceKey,
				"country": country
			}
		}).done(function(html, total){

			// Reload the page to show the updated invoice
			window.location.reload();

		}).fail(function(message) {
			PayPlans.dialog({
				content: message
			});

		});

		return;
	});

});
