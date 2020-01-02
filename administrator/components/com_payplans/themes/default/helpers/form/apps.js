PayPlans.require()
.script('shared/select2')
.done(function($) {

	$('[data-app-<?php echo $name; ?>]').select2({
		'width': 'resolve',
		'minimumResultsForSearch': Infinity
	});

});
