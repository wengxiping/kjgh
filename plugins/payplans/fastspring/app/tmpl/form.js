PayPlans.ready(function($) {

	var tags = {		
		"key": "<?php echo $payment->getKey();?>"
	};
			
	fastspring.builder.push({'tags': tags});
		
	window.fsOrderId = null;
	
	window.popupEventReceived = function(data) {
		if (data['fsc-order-id'] !== undefined) {
			window.fsOrderId = data['fsc-order-id'];
		}
	};

	window.onPopupClose = function(data) {
		if (data == undefined) {
			return;
		}
			
		if (data.reference) {
			window.location = "<?php echo rtrim(JURI::base(), '/') . $redirect; ?>";
			return;
		}
	};
});