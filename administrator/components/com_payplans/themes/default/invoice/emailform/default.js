PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			window.location = "<?php echo $return;?>";
			return;
		}

		$.Joomla('submitform', [task]);
	});
});