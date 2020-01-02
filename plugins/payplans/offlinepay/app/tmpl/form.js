PayPlans.require()
.script('site/floatlabels')
.done(function($) {
	var transactionInput = $('[data-offline-transaction-id]');
	var transactionType = $('[data-offline-transaction-type]');

	transactionType.on('change', function() {
		var value = $(this).val();

		if (value == 'Cheque' || value == 'DD') {
			transactionInput.removeClass('t-hidden');
			return;
		}

		transactionInput.addClass('t-hidden');
	});
});
