PayPlans.ready(function($) {
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=discounts&layout=form';
			return;
		}

		if (task == 'discounts.generator') {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=discounts&layout=form&generator=1';
			return;	
		}

		$.Joomla('submitform', [task]);
	});
});
