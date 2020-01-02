PayPlans.require()
.done(function($) {

	$('[data-country-select]').on('change', function() {
		var select = $(this);

		if (select.val() == "-1") {
			// unselect all the other options.
			select.find("option:selected").removeAttr("selected");
			select.find("option:selected").prop("selected", false);

			// now reselect the all country
			select.find('option[value="-1"]').attr("selected",true);
			select.find('option[value="-1"]').prop("selected",true);
		} else {
			select.find('option[value="-1"]').removeAttr("selected");
			select.find('option[value="-1"]').prop("selected",false);
		}
	});

});
