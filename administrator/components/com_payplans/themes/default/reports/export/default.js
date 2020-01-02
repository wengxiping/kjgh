PayPlans.require()
.done(function($) {
	
	$('[data-export-report-type]').on('change', function() {
		var type = $(this).val();
		var invoice = type == 'invoice';

		$('[data-invoice-status-wrapper]').toggleClass('t-hidden', !invoice);
		$('[data-payment-gateway-wrapper]').toggleClass('t-hidden', !invoice);
		$('[data-subscription-status-wrapper]').toggleClass('t-hidden', invoice);
	});
});