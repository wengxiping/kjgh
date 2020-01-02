PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'group.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=group', false);?>";
			return;
		}

		$.Joomla('submitform', [task]);
	});

		
});