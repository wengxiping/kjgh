EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=ads';
			return false;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-time-limit]').on('change', function() {
		var input = $(this);
		var checked = input.is(':checked');

		$('[data-start-date]').toggleClass('t-hidden', !checked);
		$('[data-end-date]').toggleClass('t-hidden', !checked);
	});

});
