PayPlans.require()
.done(function($) {



	// listening to euvat.showerror trigger
	$(window).on('euvat.showerror', function(event, errMsg) {
		$('[data-pp-company-message]').addClass('t-text--danger');
		$('[data-pp-company-message]').show();
		$('[data-pp-company-message]').html(errMsg);
	});

	$(window).on('euvat.processing', function(event) {
		// show loader
		$('[data-pp-company-loader]').addClass('is-active');

		// show proper message
		$('[data-pp-company-message]').removeClass('t-text--danger');
		$('[data-pp-company-message]').show();
		$('[data-pp-company-message]').html('<?php echo JText::_('COM_PP_APP_EUVAT_PROCESSIN_TAX', true); ?>');
	});

	// listening to euvat.always trigger from ajax
	$(window).on('euvat.complete', function(event, err) {

		//update message
		if (err == '') {
			$('[data-pp-company-message]').hide();
			$('[data-pp-company-message]').html('');
		}

	});

	// listening to euvat.always trigger from ajax
	$(window).on('euvat.always', function(event) {
		// turn off the loader
		$('[data-pp-company-loader]').removeClass('is-active');
	});

	$('[data-pp-company-bizname]').on('keyup', function() {
		$(window).trigger('euvat.updateBizName', $(this).val());

		delayedCheck();
	});

	$('[data-pp-company-vatno]').on('keyup', function() {
		$(window).trigger('euvat.updateVatNo', $(this).val());

		delayedCheck();
	});

	$('[data-pp-company-country]').on('change', function() {
		$(window).trigger('euvat.updateCountry', $(this).val());

		processVAT();
	});

	$('[data-pp-transaction-purpose]').on('change', function() {

		var purposeId = 1
		if ($(this).val() == 'business') {
			purposeId = 2;
		}

		$(window).trigger('euvat.updatePurpose', purposeId);

		processVAT();
	});

	var delayedCheck = $.debounce(function() {
		processVAT();
	}, 400);


	var processVAT = function () {

		// lets check if euvat is required or not.
		// check based on the euvat update button
		if ($('[data-pp-euvat-update]').length == 0) {
			return false;
		}


		var purpose = $('[data-pp-transaction-purpose]').val();

		var run = true;

		if (purpose == 'none') {
			run = false;
		}

		if (purpose == 'business') {

			if ($('[data-pp-company-bizname]').val() == '') {
				run = false;
			}

			if ($('[data-pp-company-vatno]').val() == '') {
				run = false;
			}

			if ($('[data-pp-company-country]').val() == '') {
				run = false;
			}

		}

		if (run) {

			// clear message if any
			$('[data-pp-company-message]').hide();
			$('[data-pp-company-message]').html('');

			var purposeId = 0;

			if (purpose == 'personal') {
				purposeId = 1;
			}

			if (purpose == 'business') {
				purposeId = 2;
			}

			var data = {
				isTrigger: true,
				country: $('[data-pp-company-country]').val(),
				purpose: purposeId,
				bizname: $('[data-pp-company-bizname]').val(),
				vatno: $('[data-pp-company-vatno]').val()
			}

			// if (window.euvat != undefined) {
			// 	window.euvat.process(data);
			// }

			$(window).trigger('euvat.process', data);
		}
	};

	$('[data-pp-checkout-form]').on('submit', function(ev) {
		var purpose = $('[data-pp-transaction-purpose]').val();

		// check if country selected or not.
		if (purpose == 'none') {
			var errorMsg = "<?php echo JText::_('COM_PP_APP_EUVAT_PLEASE_SELECT_PURPOSE', true); ?>";

			$('[data-pp-company-message]').html(errorMsg);

			// trigger showerror
			$(window).trigger('euvat.showerror', errorMsg);

			// focus on country selection
			$('[data-pp-transaction-purpose]').focus();

			return false;
		}
	});

});
