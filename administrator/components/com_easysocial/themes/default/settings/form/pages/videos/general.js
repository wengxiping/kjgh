EasySocial.ready(function($) {

	$('[data-video-uploads]').on('change', function() {
		var checkbox = $(this);
		var checked = checkbox.is(':checked');

		$('[data-video-encoding]').toggleClass('t-hidden', !checked);
	});

	$('[data-video-cpu-limit]').on('change', function() {
		var checked = $(this).is(':checked');

		$('[data-es-video-threads]').toggleClass('t-hidden', !checked);
	});
});