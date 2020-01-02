PayPlans.require()
.done(function($) {

	$('[data-menulit-all]').click(function(ev){
		$('[data-menu-item]').attr('checked', true);
		$('[data-menu-item]').prop('checked', true);
	});

	$('[data-menulit-none]').click(function(ev){
		$('[data-menu-item]').attr('checked', false);
		$('[data-menu-item]').prop('checked', false);
	});

});
