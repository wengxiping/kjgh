PayPlans.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		$.Joomla('submitform', [task]);
	});

	$('[data-discount-coupon-type]').on('change', function(event) {
		var element = $(this);
		var value = element.val();
		

		this.showOption = function(types) {
			var options = $('[data-discount-options]');
			
			options.addClass('t-hidden');

			options.each(function() {
				var option = $(this);
				var type = option.data('discount-options');

				if ($.inArray(type, types) !== -1) {
					option.removeClass('t-hidden');
				}
			});
		};

		if (value == 'firstinvoice' || value == 'eachrecurring') {
			this.showOption(['code', 'amount', 'type']);

			return;
		}

		if (value == 'autodiscount_onrenewal') {
			this.showOption(['preexpiry', 'postexpiry', 'type']);

			return;
		}

		if (value == 'autodiscount_onupgrade' || value == 'autodiscount_oninvoicecreation') {
			this.showOption(['amount', 'type']);
			return;
		}

		if (value == 'discount_for_time_extend') {
			this.showOption(['extendtime']);
			return;
		}
	});
});
