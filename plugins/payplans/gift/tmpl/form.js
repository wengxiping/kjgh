PayPlans.ready(function($) {

	$('[data-pp-gift-purchase]').on('click', function() {

		PayPlans.dialog({
			"content": PayPlans.ajax('site/controllers/app/trigger', {
				"event": "onPayplansGiftShowDialog",
				"event_args": {
					"invoice_key": "<?php echo $invoice->getKey();?>"
				}
			}),
			"bindings": {
				"{submitButton} click": function() {
					$('[data-pp-gift-error]').addClass('t-hidden');
					
					PayPlans.ajax('site/controllers/app/trigger', {
						"event": "onPayplansAddItemRequest",
						"event_args": {
							"invoice_key": "<?php echo $invoice->getKey();?>",
							"quantity": this.quantity().val()
						}
					}).done(function() {

						window.location.reload();
					}).fail(function(message) {
						$('[data-pp-gift-error]').html(message).removeClass('t-hidden');
					});
				}
			}
		});
	});
});


