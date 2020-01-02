PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'notifications.cancel') {
			window.location = '<?php echo JURI::base();?>index.php?option=com_payplans&view=notifications&layout=templates';
			return;
		}

		$.Joomla('submitform', [task]);
	});
});