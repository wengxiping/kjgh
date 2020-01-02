PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		 if (task == 'notifications.cancel') {
			window.location = '<?php echo JURI::base();?>index.php?option=com_payplans&view=notifications';
			return;
		}

		// simple validation
		if (! ruleValidation()) {
			return false;
		}

		$.Joomla('submitform', [task]);
	});

	function ruleValidation() {

		var msg = [];
		var isValid = true;

		if ($('input[name="title"]').val() == '') {
			msg.push('<?php echo JText::_('COM_PP_EMPTY_TITLE', true); ?>');
			isValid = false;
		}

		if (!isValid) {
			var content = msg.join('<br>');
			PayPlans.dialog({
				title: '<?php echo JText::_('COM_PP_WARNING', true);?>',
				content: content
			});
		}

		return isValid;
	};

});
