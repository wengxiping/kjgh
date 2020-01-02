PayPlans.ready(function($) {

	// Toggle options between achieving type
	$('[data-import-sample]').on('click', function() {

		// Disable the button
		$(this).attr('disabled', 'true');

		// Do the migration
		importSampleData();
	});

	var importSampleData = function() {

		// Get the type from the dropdown
		var type = $('[data-import-sample-type]').val();

		PayPlans.ajax('admin/views/migrator/importSampleData', {"type": type})
				.done(function(command) {
					$('[data-import-sample]').removeAttr('disabled');
					$('[data-import-sample]').html("<?php echo JText::_('Completed.') ?>");
				})
				.fail(function(error) {
					
				});
	}

});