
PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'export') {
			// Do the migration
			window.exportLogs();
			return;			
		}
	});

	window.exportLogs = function() {

		var list = $('[data-progress-list]');

		PayPlans.ajax('admin/views/log/exportLogs', {})
			.done(function(command, logIds) {

				if (command == 'nodata') {
					list.html("No logs to export currently.");
					return;
				}

				if (command == 'next') {
					window.exportLogs();
					return;
				}

				list.append("Logs have been exported successfully.");
			})
			.fail(function(error) {
				
			});
	}

});