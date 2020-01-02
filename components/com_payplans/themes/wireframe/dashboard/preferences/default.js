PayPlans.ready(function($) {

	<?php if ($this->config->get('discounts_referral')) { ?>
	$('[data-pp-referral-copy]').on('click', function() {
		var temp = $('<input>');
		var code = $('[data-pp-referral-code]').val();

		$('body').append(temp);
		temp.val(code).select();

		document.execCommand('copy');
		temp.remove();
	});
	<?php } ?>

		$('select[id=business_purpose').on('change', function() {
		var selected = $(this).val();

		$('[data-userpreference-business]').addClass('t-hidden');
		
		if (selected == 'business') {
			$('[data-userpreference-business]').removeClass('t-hidden');
			return;
		}
	});
});