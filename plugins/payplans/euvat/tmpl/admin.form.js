PayPlans.ready(function($) {

	var euvat = {
		"process" : function () {

			var invoiceKey = $('[data-pp-invoice-key]').val();
			var country = $('[data-pp-euvat-country]').val();
			var purpose = $('[data-pp-euvat-purpose]').val();
			var bizname = $('[data-pp-euvat-businessname]').val();
			var vatno = $('[data-pp-euvat-vatnumber]').val();

			// disable the update button
			$('[data-pp-euvat-update]').attr('disabled', 'disabled');
			$('[data-pp-euvat-update]').prop('disabled', true);

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

				if (err) {
					$('[data-pp-euvat-message]').html(err);
				} else {

					// Reload the page to show the updated invoice
					window.location.reload();

				}


			}).fail(function(message) {
				PayPlans.dialog({
					content: message
				});
			}).always(function() {

				// enable the update button
				$('[data-pp-euvat-update]').removeAttr('disabled');
				$('[data-pp-euvat-update]').prop('disabled', false);

			});

		},
		"validate" : function () {

			$('[data-pp-euvat-message]').html('');

			// check if country selected or not.
			if ($('[data-pp-euvat-country]').val() == '0') {
				var errorMsg = "<?php echo JText::_('COM_PP_APP_EUVAT_PLEASE_SELECT_COUNTRY', true); ?>";

				$('[data-pp-euvat-message]').html(errorMsg);

				// focus on country selection
				$('[data-pp-euvat-country]').focus();

				return false;
			}

			// check if country selected or not.
			if ($('[data-pp-euvat-purpose]').val() == '0') {
				var errorMsg = "<?php echo JText::_('COM_PP_APP_EUVAT_PLEASE_SELECT_PURPOSE', true); ?>";

				$('[data-pp-euvat-message]').html(errorMsg);

				// focus on country selection
				$('[data-pp-euvat-purpose]').focus();

				return false;
			}


			var purpose = $('[data-pp-euvat-purpose]').val();
			if (purpose == '<?php echo PP_EUVAT_PURPOSE_BUSINESS; ?>') {

				if ($('[data-pp-euvat-businessname]').val() == '' || $('[data-pp-euvat-vatnumber]').val() == '') {

					var errorMsg = "<?php echo JText::_('COM_PP_APP_EUVAT_PLEASE_ENTER_NAME_AND_VAT', true); ?>";

					$('[data-pp-euvat-message]').html(errorMsg);

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


});
