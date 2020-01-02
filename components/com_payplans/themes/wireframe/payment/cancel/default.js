PayPlans.ready(function($) {

	$(document).on('click', '[data-pp-contact]', function(){

		PayPlans.dialog({
			content: PayPlans.ajax('site/views/contact/form')
		});

	});
});