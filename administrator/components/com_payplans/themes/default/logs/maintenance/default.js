PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'fix') {
			// Do the migration
			window.runMaintenance();
			return;			
		}
	});

	window.runMaintenance = function() {
		var items = <?php echo json_encode($files); ?>;
		var requests = [];
		var list = $('[data-progress-list]');

		$.each(items, function(i, file) {

			requests.push(PayPlans.ajax('admin/views/log/fixLegacyFile', {
				"file": encodeURIComponent(file)
			}).done(function() {
				

			}));
		});

		$.when.apply(null, requests).done(function() {
			$('[data-result-complete]').removeClass('t-hidden');
		});
	}

});