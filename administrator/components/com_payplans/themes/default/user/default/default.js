PayPlans.ready(function($) {

	<?php if ($this->tmpl != 'component') { ?>
	window.selectPlan = function(obj) {

		$('[data-apply-plan-id]').val(obj.id);
		
		$.Joomla('submitform', ['user.applyPlan']);

		PayPlans.dialog().close();

		$('.pp-backend').addClass('is-loading');
	};

	$.Joomla("submitbutton", function(action) {

		if (action == 'user.browsePlan') {

			PayPlans.dialog({
				content: PayPlans.ajax('admin/views/plan/browse', {"jscallback": "selectPlan"}),
			});

			return;
		}

		$.Joomla('submitform', [action]);
	});
	<?php } ?>

	<?php if ($this->tmpl == 'component') { ?>
		$('[data-pp-user-item]').on('click', function(event) {
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
