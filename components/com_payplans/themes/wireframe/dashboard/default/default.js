PayPlans.ready(function($) {

	$(document).on('click.cancel.subs', '[data-cancel-subscription]', function() {
		var element = $(this);
		var key = element.data('key');

		PayPlans.dialog({
			"content": PayPlans.ajax('site/views/order/confirmCancellation', {
				"order_key": key
			})
		})
	});

	$(document).on('click.delete.subs', '[data-delete-subscription]', function() {
		var element = $(this);
		var key = element.data('key');

		PayPlans.dialog({
			"content": PayPlans.ajax('site/views/order/confirmDeleteion', {
				"order_key": key
			})
		})
	});


	$(document).on('click.upgrade.subs', '[data-upgrade-button]', function() {
		var element = $(this);
		var key = element.data('key');

		PayPlans.dialog({
			content: PayPlans.ajax('site/views/order/confirmUpgrade', {
				'key' : key
			}),
			bindings: {
				'{planSelection} change' : function(i, e) {

					var selectedPlan = $(i).val();

					// we need to recalculate the un-utilized amount.
					PayPlans.ajax('site/views/order/showUpgradeDetails', {
						"key" : key,
						"id" : selectedPlan
					})
					.done(function(item) {

						// show the pricing accordingly.
						$('[data-upgrade-amount]').html(item.price);
						$('[data-ununtilized-amount]').html(item.unutilized);
						$('[data-ununtilized-tax]').html(item.unutilizedTax);
						$('[data-payable-amount]').html('<b>' + item.payableAmount + '</b>');

					});
				}
			}
		});

	});

});
