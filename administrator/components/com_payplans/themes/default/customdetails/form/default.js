PayPlans.ready(function($) {

	$('input[name=params\\[applyAll\\]]').trigger('change');

	$.Joomla('submitbutton', function(task) {

		if (task == 'customdetails.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=user&layout=customdetails', false);?>";
			return;
		}

		// simple validation
		if (! appValidation()) {
			return false;
		}

		$.Joomla('submitform', [task]);
	});

	function appValidation() {

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