PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			<?php if ($from) { ?>
				window.location = "<?php echo $from;?>";
				return;
			<?php } ?>
			
			window.location = "<?php echo JURI::base();?>index.php?option=com_payplans&view=transaction";
			return;
		}

		$.Joomla('submitform', [task]);
	});
});