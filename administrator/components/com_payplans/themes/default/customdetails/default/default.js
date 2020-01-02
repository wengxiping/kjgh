PayPlans.ready(function($) {
	
	$.Joomla('submitbutton', function(task) {
		
		if (task == 'add') {
			window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_payplans&view=<?php echo $view; ?>&layout=customdetailsform';
			return;
		}

		$.Joomla('submitform', [task]);
	});
});
