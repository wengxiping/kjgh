PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			window.location = "<?php echo JURI::base();?>index.php?option=com_payplans&view=resource";
			return;
		}
		
		$.Joomla('submitform', [task]);
	});
});