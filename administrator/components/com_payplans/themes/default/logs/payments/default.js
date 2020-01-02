PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		if (task == 'log.purgeIpn') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/log/purgeConfirmation'),
				"bindings": {
					"{submitButton} click": function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-simulate]').on('click', function(event) {
		event.stopPropagation();

		var element = $(this);
		var id = element.parents('[data-item]').data('id');

		PayPlans.dialog({
			"content": PayPlans.ajax('admin/views/log/simulatePaymentNotification', {
				"id": id
			})
		});

	});

	$('[data-view-notification]').on('click', function(event) {
		event.stopPropagation();

		var element = $(this);
		var type = element.data('view-notification');
		var id = element.parents('[data-item]').data('id');

		PayPlans.dialog({
			"content": PayPlans.ajax('admin/views/log/viewPaymentNotification', {
				"id": id,
				"type": type
			})
		});

	});
});