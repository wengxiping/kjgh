PayPlans.ready(function($) {

	$(document).on('click.pp.select.add.options', '[data-select-add]', function() {
		var parent = $(this).parents('[data-select-row]');
		var newItem = $(parent).clone();

		$(newItem).find('[data-select-title]').val('');
		$(newItem).find('[data-select-price]').val('');
		$(newItem).find('[data-select-remove]').removeClass('t-hidden');

		$('[data-select-container]').append(newItem);
	});

	$(document).on('click.pp.select.remove.options', '[data-select-remove]', function() {

		var parent = $(this).parents('[data-select-row]');

		$(parent).remove();
	});
});