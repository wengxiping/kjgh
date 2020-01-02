PayPlans.ready(function($) {
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=modifiers&layout=form';
			return;
		}

		if (task == 'modifiers.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=modifiers', false);?>";
			return;
		}

		$.Joomla('submitform', [task]);
	});
});
