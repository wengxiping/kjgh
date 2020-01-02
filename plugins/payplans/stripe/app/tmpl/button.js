PayPlans.ready(function($) {

	if (window.updateStripe == undefined) {

		window.updateStripe = function(appId, subscriptionKey, publicKey) {
			PayPlans.dialog({
				"content": PayPlans.ajax('plugins/stripe/updateForm', {
					"appId": appId,
					"subscriptionKey": subscriptionKey
				})
			});
		};
	}

	$('[data-stripe-update-<?php echo $uid;?>]').on('click', function() {
		window.updateStripe("<?php echo $appId;?>", "<?php echo $subscription->getKey();?>");
	});
});