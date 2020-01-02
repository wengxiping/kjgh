PayPlans.ready(function($) {

	<?php if ($this->tmpl == 'component') { ?>
		$('[data-pp-row]').on('click', function(event) {
			event.preventDefault();

			var item = $(this);
			var obj = {
					'id': item.data('id'),
					'title': item.data('title'),
					'orderId': item.data('order-id')
			};

			window.parent['<?php echo JRequest::getCmd('jscallback');?>'].apply(null, [obj]);
		});
	<?php } ?>

	<?php if ($this->tmpl != 'component') { ?>
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=subscription&layout=form';
			return;
		}

		if (task == 'updateStatus') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/subscription/updateStatus', {"cid": PayPlans.getSelectedIds()})
			});
			return;
		}

		if (task == 'extend') {

			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/subscription/extend', {"cid": PayPlans.getSelectedIds()})
			});

			return;
		}

		$.Joomla('submitform', [task]);
	});
	<?php } ?>
});
