PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'upgrade') {
			return true;
		}

		if (task == 'subscription.apply' || task == 'subscription.save' || task == 'subscription.saveNew') {
			var hasErrors = $('[data-pp-form]').validateForm();

			if (!hasErrors) {
				$.Joomla('submitform', [task]);
				return true;
			}
			
			return false;
		}

		if (task == 'newinvoice') {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&task=order.createInvoice&id=<?php echo $order->getId();?>';
			return;
		}

		if (task == 'newrecurringinvoice') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/subscription/confirmAddInvoice', {
					"id": "<?php echo $order->getId();?>"
				})
			});

			return;
		}


		if (task == 'subscription.terminate') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/subscription/confirmCancel', {
					"id": "<?php echo $order->getId();?>"
				})
			});

			return;
		}

		$.Joomla('submitform', [task]);

	});

	<?php if ($subscription->canUpgrade()) { ?>
	// Define actions when the upgrade button is clicked
	$(document).on('click.upgrade', '#toolbar-upgrade', function(event) {

		var data = { 
				'userId': "<?php echo $order->getBuyer()->getId();?>",
				'orderId': "<?php echo $order->getId();?>"
		};

		PayPlans.dialog({
			"content": PayPlans.ajax('admin/views/upgrades/upgrade', data)
		});
	});
	<?php } ?>
});