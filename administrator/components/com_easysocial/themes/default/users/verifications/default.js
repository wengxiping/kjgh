
EasySocial
.ready(function($){

	$.Joomla('submitbutton', function(task) {
		$.Joomla('submitform', [task]);
	});

	$('[data-verify-message]').on('click', function() {
		var id = $(this).data('id');

		EasySocial.dialog({
			content : EasySocial.ajax('admin/views/users/viewVerificationMessage', {
				"id" : id
			})
		})
	});
});