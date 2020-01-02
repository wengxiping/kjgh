PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		if (task == 'plan.new') {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=plan&layout=form';
			return;
		}

		$.Joomla('submitform', [task]);
	});

	<?php if ($this->tmpl == 'component') { ?>
		$('[data-pp-plan-item]').on('click', function(event) {
			event.preventDefault();


			var item = $(this);
			var obj = {
					'id': item.data('id'),
					'title': item.data('title')
			};

			window.parent['<?php echo JRequest::getCmd('jscallback');?>'].apply(null, [obj]);
		});
	<?php } ?>
});
