PayPlans.ready(function($) {

	$('[data-profile-source]').change(function(ev) {
		var source = $(this).val();
		
		PayPlans.ajax('admin/views/assigns/renderProfileDropdown', {
			"source": source
		})
		.done(function(html) {
			$('[data-profile-select]').html(html);
		})
		.fail(function(message) {
			$('[data-profile-select]').html(message);
		});

	});
});