PayPlans.ready(function($) {

	var titleField = $('[data-pp-form-user-preview]');
	var valueField = $('[data-pp-form-user-input]');
	var browseButton = $('[data-pp-form-user-browse]');
	var cancelButton = $('[data-pp-form-user-clearl]');

	window.selectUser = function(obj) {
		
		titleField.val(obj.title);
		valueField.val(obj.id);

		// Close the dialog when done
		PayPlans.dialog().close();
	};

	cancelButton.on('click', function() {
		valueField.val('');
	});

	browseButton.on('click', function() {
		PayPlans.dialog({
			content: PayPlans.ajax('admin/views/user/browse', {"jscallback": "selectUser" })
		});
	});
});
