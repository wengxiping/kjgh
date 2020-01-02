PayPlans.ready(function($) {

	$('[data-pp-rewriter]').on('click', function() {

		PayPlans.dialog({
			"content": PayPlans.ajax('admin/views/rewriter/view')
		});
	});
});