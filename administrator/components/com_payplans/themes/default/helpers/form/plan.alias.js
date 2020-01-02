PayPlans.ready(function($) {
	var wrapper = $('[data-plan-listing]');
	var template = $('[data-plan-template]');
	var totalValues = <?php echo $totalValues > 0 ? $totalValues - 1 : 0;?>;
	var name = "<?php echo $name;?>";

	console.log(totalValues, name);;

	$(document).on('click.insert.row', '[data-insert-row]', function() {
		var element = $(this);
		var row = template.clone();
		var plan = row.find('select');
		var textbox = row.find('input[type=text]');

		var planInputName = name + '[' + (totalValues + 1) + '][]';

		plan.attr('name', planInputName);
		textbox.attr('name', planInputName);

		totalValues += 1;

		row.removeClass('t-hidden')
			.appendTo(wrapper);
	});

	$(document).on('click.remove.row', '[data-remove-row]', function() {
		var element = $(this);
		var parent = element.parents('[data-plan-item]');

		parent.remove();

	});
});