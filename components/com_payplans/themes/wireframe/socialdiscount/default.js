PayPlans.ready(function($) {

	window.requestSocialDiscount = function(media) {

		if (!media) {
			return false;
		}

		PayPlans.ajax('site/views/discounts/check', {
			"socialMedia": media,
			"invoice_key": "<?php echo $invoice->getKey();?>"
		}).done(function() {
			window.location.reload();
		});
	};

});