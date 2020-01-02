PayPlans.ready(function($) {

	$('[data-pp-aupdiscount-apply]').on('click', function() {
		var points = $('[data-pp-aupdiscount-points]').val();
		var invoiceId = $(this).data('id');

		// 	var currentAup = xi.jQuery('#auppoints').html();

		PayPlans.ajax('site/controllers/app/trigger', {
			"event": "onPayplansAupDiscountRequest",
			"event_args": {
				"invoiceId": invoiceId,
				"points": points
			}
		}).done(function(){

			// Reload the page to show the updated invoice
			window.location.reload();

		}).fail(function(message) {
			$('[data-pp-aupdiscount-message]').html(message);
		});

		return;
	});

});