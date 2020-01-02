PayPlans.require()
.script('shared/select2')
.done(function($) {

	$('[data-group-<?php echo $name; ?>]').select2({
		'width': 'resolve',
		'minimumResultsForSearch': Infinity
	});

});
