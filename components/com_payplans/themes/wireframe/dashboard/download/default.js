PayPlans.ready(function($) {

	$('[data-pp-download-request]').on('click', function() {
		var button = $(this);

		PayPlans
			.ajax('site/views/download/request')
			.done(function() {
				$('[data-pp-download-response]').html('<?php echo JText::_('COM_PAYPLANS_FRONT_END_DASHBOARD_USER_DOWNLOAD_REQUEST_DATA_SUCCESS', true);?>');
				button.addClass('t-hidden');
			});
	});
});