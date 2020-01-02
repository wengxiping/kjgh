PayPlans.ready(function($) {

	<?php if ($this->tmpl == 'component') { ?>
		$('[data-pp-row]').on('click', function(event) {
			event.preventDefault();

			var item = $(this);
			var obj = {
					'id': item.data('id')
			};

			window.parent['<?php echo JRequest::getCmd('jscallback');?>'].apply(null, [obj]);
		});
	<?php } ?>

	<?php if ($this->tmpl != 'component') { ?>
	window.selectSubscription = function(obj) {
			
		window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&task=order.createInvoice&id=' + obj.orderId;

		// Close the dialog when done
		PayPlans.dialog().close();
	};

	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/subscription/browse', {
					'jscallback': 'selectSubscription'
				})
			});

			return true;
		}

		$.Joomla('submitform', [task]);
	});
	<?php } ?>
});