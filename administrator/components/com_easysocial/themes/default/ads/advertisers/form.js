EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=ads&layout=advertisers';
			return false;
		}

		$.Joomla('submitform', [task]);
	});
});
