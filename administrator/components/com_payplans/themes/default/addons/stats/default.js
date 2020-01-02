PayPlans.ready(function($) {

	var lock = true;

	$('[data-status-list]').change(function(ev) {

		var id = $(this).data('id');
		var status = $(this).val();

		PayPlans.ajax('admin/controllers/addons/updateStatStatus', {
			"id": id,
			"status": status
		})
		.done(function(msg) {
			PayPlans.dialog({
				content: msg
			});
		});

	});
});
