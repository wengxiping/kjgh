PayPlans.require()
.done(function($) {
	$('[data-number-of-purchase]').on('change', function() {
		var wrapper = $(this).parents('[data-plan-footer]');

		// Reset the priceset selection
		wrapper.find('[data-priceset-selection]').prop("checked", false);

		var n = $(this).val(),
			pricePerDay = wrapper.find('[data-plan-advancedpricing]').data('price-perday'),
			rows = $('[data-price-set]');
		
		wrapper.find('[data-price-set]').each(function(i, el) {
			var days = $(el).data('days'),
				pricePerUnit = $(el).data('price');

			var actualPrice = (pricePerDay * days) * n;
			$(el).find('[data-actual-price] .pp-amount').html(actualPrice.toFixed(2));
			
			var priceToPay = pricePerUnit * n;
			$(el).find('[data-price-topay] .pp-amount').html(priceToPay.toFixed(2));

			var savings = actualPrice - priceToPay;
			$(el).find('[data-savings] .pp-amount').html(savings.toFixed(2));

			$(el).find('[data-priceset-selection]').val(priceToPay);
		});
	});

	$('[data-priceset-selection]').on('change', function() {
		var wrapper = $(this).parents('[data-plan-footer]'),
			price = $(this).val(),
			duration = $(this).data('duration'),
			unit = wrapper.find('[data-number-of-purchase]').val(),
			subscribeButton = wrapper.find('[data-subscribe-button]'),
			value = unit + '_' + price + '_' + duration;

		// if the advance pricing radio is not check, check it.
		wrapper.find('[data-advancedpricing-radio]').prop("checked", true);

		resetLink(subscribeButton, value);
	});

	$('[data-advancedpricing-radio]').on('change', function() {
		var subscribeButton = $(this).parents('[data-plan-footer]').find('[data-subscribe-button]');
		resetLink(subscribeButton, '');
	});

	var resetLink = function(button, value) {
		
		var defaultLink = button.data('default-link');
		
		if (value.length > 0) {
			var separator = defaultLink.indexOf("?") == -1 ? '?' : '&';
			defaultLink += separator + 'advpricing=' + value;
		}

		button.attr("href", defaultLink);
	}
});