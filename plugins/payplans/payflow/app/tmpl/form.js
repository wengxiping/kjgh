PayPlans.ready(function($) {
	var form = $('[data-payflow-form]');

	$('[data-submit-payment]').on('click', function(event) {
		event.preventDefault();

		var cardType = $('[data-payflow-card-type]');
		var card = form.find('.card-js');

		cardType.val(card.CardJs('cardType'));
		form.submit();
	});
});