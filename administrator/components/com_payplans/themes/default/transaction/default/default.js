PayPlans.ready(function($) {

	window.selectInvoice = function(obj) {
		window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=transaction&layout=form&invoice_id=' + obj.id;

		// Close the dialog when done
		PayPlans.dialog().close();
	};

	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/invoice/browse', {
					"jscallback": "selectInvoice"
				})
			});

			return true;
		}

		$.Joomla('submitform', [task]);
	});
});