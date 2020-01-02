PayPlans.ready(function($) {


	$.Joomla('submitbutton', function(task) {

		if (task == 'plan.apply' || task == 'plan.save' || task == 'plan.saveNew') {
			var hasErrors = $('[data-pp-form]').validateForm();

			if (!hasErrors) {
				$.Joomla('submitform', [task]);
				return true;
			}

			return false;
		}


		if (task == 'plan.cancel') {
			window.location = "<?php echo JRoute::_('index.php?option=com_payplans&view=plan', false);?>";
			return;
		}
	});


	// Toggle options between achieving type
	$('[data-recurr-validate]').on('click', function() {

		var id = $('input[name="plan_id"]').val();

		PayPlans.dialog({
			content: PayPlans.ajax('admin/views/plan/recurrencevalidation', {
				'id' : id
			})
		});

	})

		$('[fixed-expiration-type]').on('change', function() {
		var type = $(this).val();
		var value = type == 'fixed';

		$('[data-fixed-expiration-wrapper]').toggleClass('t-hidden', !value);
	});

});
