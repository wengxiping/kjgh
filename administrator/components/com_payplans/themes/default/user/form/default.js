PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_payplans&view=user';
			return;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-toggle=tab]').on('click', function() {
		var element = $(this);
		var link = element.attr('href');
		var active = link.replace('#', '');
		
		var hidden = $('[data-pp-active-tab]');

		if (hidden.length > 0) {
			hidden.val(active);
		}
	});
});