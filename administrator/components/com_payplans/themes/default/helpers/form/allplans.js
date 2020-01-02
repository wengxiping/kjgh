PayPlans.ready(function($) {
	
	$('[<?php echo $uid;?>]').on('change', function() {	
		var checked = $(this).is(':checked');
		var elements = $('<?php echo $dependencies;?>');

		if (checked) {
			elements.addClass('t-hidden');
			return;
		}

		elements.removeClass('t-hidden');
	});

});