PayPlans.ready(function($) {

	<?php if ($print) { ?>
		window.print();
	<?php } ?>
	
	$('[data-invoice-print]').on('click', function() {
		window.print();
	});
});