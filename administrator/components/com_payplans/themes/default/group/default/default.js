PayPlans.ready(function($) {
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=group&layout=form';
			return;
		}

		if (task == 'group.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=group', false);?>";
			return;
		}

		$.Joomla('submitform', [task]);
	});
});
