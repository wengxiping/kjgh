PayPlans.ready(function($) {

	var threeDSecure;

	braintree.dropin.create({
		authorization: '<?php echo $token;?>',
		container: '#dropin-container',
		threeDSecure: {
			amount: '<?php echo $amount; ?>'
		},
		paypal: {
    		flow: 'checkout',
    		amount: '<?php echo $amount;?>',
    		currency: '<?php echo $currency;?>'
  		}
	});

	$('#pp-payment-app-buy').click(function(){

	});


});