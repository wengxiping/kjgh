PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'log.purge') {

			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/log/purge'),
				"bindings": {
					"{submitButton} click": function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return;
		}

		if (task == 'log.remove') {
			$.Joomla('submitform', [task]);
		}
	});


});