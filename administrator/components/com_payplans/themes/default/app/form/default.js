PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'app.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=app', false);?>";
			return;
		}

		if (task == 'gateways.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=gateways', false);?>";
			return;
		}

		if (task == 'automation.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=automation', false);?>";
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
		var wrapperAppForm = $('[data-pp-form]');

		if ($('input[name="title"]').val() == '') {
			msg.push('<?php echo JText::_('COM_PP_EMPTY_TITLE', true); ?>');
			isValid = false;
		}

		// determine whether the app form got this offline payment selection field or not
		var hasOfflinePaymentMethodField = $(wrapperAppForm).find('[data-offlinepayment-select]').length;

		// determine whether the app form got this easysocial badges app selection field or not
		var hasEasysocialBadgeField = $(wrapperAppForm).find('[data-easysocialbadges-select]').length;

		// Only check this part if that is offline payment form
		if ($('[data-offlinepayment-select]').val() == null && hasOfflinePaymentMethodField) {
			msg.push('<?php echo JText::_('COM_PP_APP_EMPTY_OFFLINE_PAYMENT_METHOD', true); ?>');
			isValid = false;
		}

		// Only check this part if that is easysocial badges app 
		if ($('[data-easysocialbadges-select]').val() == null && hasEasysocialBadgeField) {
			msg.push('<?php echo JText::_('COM_PP_APP_EMPTY_SELECTION_FIELD', true); ?>');
			isValid = false;
		}

		if ($('[data-app-all-plans]').not('[type=checkbox]').val() == '0' && $('[data-app-selected-plans]').val() == null) {
			msg.push('<?php echo JText::_('COM_PP_APP_EMPTY_PLANS', true); ?>');
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