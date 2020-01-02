PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		 if (task == 'addons.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=addons', false);?>";
			return;
		}

		// simple validation
		if (! addonValidation()) {
			return false;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-availability]').on('change', function(event) {

		var val = $(this).val();

		if (val == '1') {
			// this is limited
			$('[data-stock-container]').removeClass('t-hidden');
		} else {
			//unlimited
			$('[data-stock-container]').addClass('t-hidden');
			$('[data-stock-input]').val('');
		}

	});

	function addonValidation() {

		var msg = [];
		var isValid = true;

		if ($('input[name="title"]').val() == '') {
			msg.push('<?php echo JText::_('COM_PP_ADDONS_EMPTY_TITLE', true); ?>');
			isValid = false;
		}

		if ($('input[name="apply_on"]').not('[type=checkbox]').val() == '0' && $('[data-plans-input]').val() == null) {
			msg.push('<?php echo JText::_('COM_PP_ADDONS_EMPTY_PLANS', true); ?>');
			isValid = false;
		}

		if ($('input[name="price"]').val() == '') {
			msg.push('<?php echo JText::_('COM_PP_ADDONS_EMPTY_PRICE', true); ?>');
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
