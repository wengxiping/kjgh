PayPlans.require()
.done(function($) {

	$('[data-export-invoice-type]').on('change', function() {
		var type = $(this).val();
		var txnDate = type == 'transactionDate';

		$('[data-invoice-transactiondate]').toggleClass('t-hidden', !txnDate);
		$('[data-invoice-limit]').toggleClass('t-hidden', !txnDate);
		$('[data-invoice-key]').toggleClass('t-hidden', txnDate);
	});
});