PayPlans.ready(function($) {

	var isSubmitting = false;

	$('[data-pp-stripe-submit]').on('click', function() {
		
		if (isSubmitting) {
			return;
		}

		// current button
		var button = $(this);

		// lock the submit button
		isSubmitting = true;

		// show loading button
		button.addClass('is-loading');

		var element = $(this);
		var publicKey = element.data('key');
		var form = $('[data-pp-stripe-form]');
		var result = $('[data-pp-stripe-result]');

		Stripe.setPublishableKey(publicKey);

		var number = $('input[name=stripe_card_num]').val();
		var cvc = $('input[name=stripe_card_code]').val();
		var expireMonth = $('input[name=stripe_exp_month]').val();
		var expireYear = $('input[name=stripe_exp_year]').val();

		var options = {
			"number": number,
			"cvc": cvc,
			"exp_month": expireMonth,
			"exp_year": expireYear
		};

		result.html('');
		result.removeClass('o-alert o-alert--danger');

		Stripe.createToken(options, function(code, response) {

			if (response.error) {
				result.html(response.error.message);
				result.addClass('o-alert o-alert--danger');


				isSubmitting = false;
				button.removeClass('is-loading');

				return;
			}

			var token = response.id;
		
			form.find('[data-stripe-token]')
				.val(token);

			form.submit();
		});
	});
});