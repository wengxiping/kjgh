
PayPlans.ready(function($) {

	$('#pp-payment-app-buy').click(function(){
			this.button('loading');
	});

	braintree.setup("<?php echo $token;?>", "dropin", {
  		container: "dropin-container"
	});
});