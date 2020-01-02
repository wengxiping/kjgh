PayPlans.ready(function($) {

	var medias = ['facebook', 'twitter'];

	$.each(medias, function(i, media) {

		$('[data-pp-' + media + ']').on('change', function() {
			var checked = $(this).is(':checked');

			if (checked) {
				$('[data-pp-discounts-' + media + ']').removeClass('t-hidden');
				return;
			}

			$('[data-pp-discounts-' + media + ']').addClass('t-hidden');
			return;
		});		
	});

});