PayPlans.ready(function($) {
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=referrals&layout=form';
			return;
		}


		$.Joomla('submitform', [task]);
	});
});
