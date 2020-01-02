PayPlans.ready(function($) {

	var euvat = {
		"lock" : false,
		"process" : function (data) {

			if (euvat.lock) {
				return;
			}

			var isTrigger = null;

			if (data != undefined) {
				isTrigger = data.isTrigger;
			}

			var invoiceKey = $('[data-pp-invoice-key]').val();
			var country = $('[data-pp-euvat-country]').val();
			var purpose = $('[data-pp-euvat-purpose]').val();
			var bizname = $('[data-pp-euvat-businessname]').val();
			var vatno = $('[data-pp-euvat-vatnumber]').val();

			if (isTrigger) {

				var country = data.country;
				var purpose = data.purpose;
				var bizname = data.bizname;
				var vatno = data.vatno;
			}

			// disable the update button
			$('[data-pp-euvat-update]').attr('disabled', 'disabled');
			$('[data-pp-euvat-update]').prop('disabled', true);

			euvat.lock = true;

			// trigger euvat.processing
			$(window).trigger('euvat.processing');

			PayPlans.ajax('site/controllers/app/trigger', {
				"event": "onPayplansTaxRequest",
				"event_args": {
					"invoice_key": invoiceKey,
					"country": country,
					"purpose": purpose,
					"businessVat": vatno,
					"businessName": bizname
				}
			}).done(function(html, total, err){

				// remove all
				$('[data-pp-modifier-discount]').remove();

				// now repopulate with the udpates
				$('[data-pp-modifiers]').prepend(html);
				$('[data-pp-payable-label]').html(total);

				if (err) {
					$('[data-pp-euvat-message]').html(err);

					// trigger showerror
					$(window).trigger('euvat.showerror', err);
				}


				// trigger euvat.complete
				$(window).trigger('euvat.complete', err);


			}).fail(function(message) {
				// $('[data-pp-euvat-message]').html(message);
			}).always(function() {

				euvat.lock = false;

				// enable the update button
				$('[data-pp-euvat-update]').removeAttr('disabled');
				$('[data-pp-euvat-update]').prop('disabled', false);

				$(window).trigger('euvat.always');

			});

		},
		"validate" : function () {

			$('[data-pp-euvat-message]').html('');

			// check if country selected or not.
			if ($('[data-pp-euvat-country]').val() == '0') {
				var errorMsg = "<?php echo JText::_('COM_PP_APP_EUVAT_PLEASE_SELECT_COUNTRY', true); ?>";

				$('[data-pp-euvat-message]').html(errorMsg);

				// trigger showerror
				$(window).trigger('euvat.showerror', errorMsg);

				// focus on country selection
				$('[data-pp-euvat-country]').focus();

				return false;
			}

			// check if country selected or not.
			if ($('[data-pp-euvat-purpose]').val() == '0') {
				var errorMsg = "<?php echo JText::_('COM_PP_APP_EUVAT_PLEASE_SELECT_PURPOSE', true); ?>";

				$('[data-pp-euvat-message]').html(errorMsg);

				// trigger showerror
				$(window).trigger('euvat.showerror', errorMsg);

				// focus on country selection
				$('[data-pp-euvat-purpose]').focus();

				return false;
			}


			var purpose = $('[data-pp-euvat-purpose]').val();
			if (purpose == '<?php echo PP_EUVAT_PURPOSE_BUSINESS; ?>') {

				if ($('[data-pp-euvat-businessname]').val() == '' || $('[data-pp-euvat-vatnumber]').val() == '') {

					var errorMsg = "<?php echo JText::_('COM_PP_APP_EUVAT_PLEASE_ENTER_NAME_AND_VAT', true); ?>";

					$('[data-pp-euvat-message]').html(errorMsg);

					// trigger showerror
					$(window).trigger('euvat.showerror', errorMsg);

					// focus on country selection
					$('[data-pp-euvat-businessname]').focus();

					return false;

				}
			}

			return true;
		}
	};

	$('[data-pp-euvat-update]').on('click', function() {
		if (euvat.validate()) {
			euvat.process();
		}
	});

	$('[data-pp-euvat-purpose]').on('change', function() {

		var purpose = $('[data-pp-euvat-purpose]').val();
		if (purpose == '<?php echo PP_EUVAT_PURPOSE_BUSINESS; ?>') {
			$('[data-pp-euvat-company]').removeClass('t-hidden');
		} else {
			$('[data-pp-euvat-company]').addClass('t-hidden');
		}

	});

	$('[data-pp-checkout-form]').on('submit', function(ev) {
		if (!euvat.validate()) {
			return false;
		}
	});


	// listening to euvat.always trigger from ajax
	$(window).on('euvat.process', function(event, data) {
		euvat.process(data);
	});

	$(window).on('euvat.updateBizName', function(event, data) {
		$('[data-pp-euvat-businessname]').val(data);
	});

	$(window).on('euvat.updatePurpose', function(event, data) {
		$('[data-pp-euvat-purpose]').val(data);
	});

	$(window).on('euvat.updateVatNo', function(event, data) {
		$('[data-pp-euvat-vatnumber]').val(data);
	});

	$(window).on('euvat.updateCountry', function(event, data) {
		$('[data-pp-euvat-country]').val(data);
	});

});
