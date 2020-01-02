PayPlans.ready(function($) {

	var isSubmitting = false;

	var stripePublicKey = '<?php echo $publicKey; ?>';
	var stripe = Stripe(stripePublicKey);

	// Create an instance of Elements.
	var elements = stripe.elements();
	var cardElement = elements.create('card');

	// Add an instance of the card Element into the `card-element` <div>.
	cardElement.mount('#card-element');

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

		//var elements = $(this);
		var form = $('[data-pp-stripe-form]');
		var result = $('[data-pp-stripe-result]');
		var paymentIntent = '<?php echo $paymentIntentSecret; ?>';
		var publicKey = '<?php echo $publicKey; ?>';
		var cardholderName = $('#cardholder-name').val();


		/*var stripe = Stripe(publicKey);
	
		var elements = stripe.elements();
		var cardElement = elements.create('card');
		cardElement.mount('#card-element');
*/
		result.html('');
		result.removeClass('o-alert o-alert--danger');

		stripe.handleCardPayment(
			paymentIntent, cardElement, {
  					payment_method_data: {
        			billing_details: {name: cardholderName}
      			}
    		}
    	).then(function(response) {
			if (response.error) {
				result.html(response.error.message);
				result.addClass('o-alert o-alert--danger');


				isSubmitting = false;
				button.removeClass('is-loading');

				return;
			}

			form.submit();
		});
	});
});
