PayPlans.require()
.done(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'invoice.cancel') {
			window.location = "<?php echo JURI::base();?>index.php?option=com_payplans&view=invoice";
			return;
		}

		if (task == 'addTransaction') {
			window.location = "<?php echo JURI::base();?>index.php?option=com_payplans&view=transaction&layout=form&invoice_id=<?php echo $invoice->getId();?>&from=<?php echo base64_encode(JRequest::getUri());?>";
			return;
		}

		if (task == 'sendInvoiceLink') {
			window.location = "<?php echo JURI::base();?>index.php?option=com_payplans&view=invoice&layout=emailform&id=<?php echo $invoice->getId();?>&from=<?php echo base64_encode(JRequest::getUri());?>";
			return;
		}

		if (task == 'invoice.paid') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/invoice/confirmPaid'),
				"bindings": {
					"{submitButton} click": function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return;
		}

		if (task == 'invoice.refund') {
			PayPlans.dialog({
				"content": PayPlans.ajax('admin/views/invoice/confirmRefund', {
					"id": "<?php echo $invoice->getId();?>"
				})
			});

			return;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-form-select-dropdown]').on('change', function() {
		var optionSelected = $('option:selected', $(this));
		var items = optionSelected.data('option-items').split(',');

		$('.o-form-group.expirationtype').addClass('t-hidden');

		$.each(items, function(key, value) {
			$('.o-form-group.expirationtype.' + value).removeClass('t-hidden');
		});
	});

	$('[data-form-select-dropdown]').trigger('change');

	$('[data-pp-discount-appy]').on('click.discount.apply', function() {

		var coupon =  $('#app_discount_code_id').val();
		var invoiceKey = $('[data-pp-invoice-key]').val();

		var discountMsg = $('[data-pp-discount-message]');
		var button = $(this);

		discountMsg.removeClass('has-error');
		discountMsg.html('');

		button.addClass('is-loading');

		PayPlans.ajax('site/views/discounts/check', {
			"code": coupon,
			"invoice_key": invoiceKey
		}).done(function(discount, total) {

			window.location.reload();

		}).fail(function(message) {
			discountMsg.addClass('has-error');
			discountMsg.html(message);

		}).always(function() {
			button.removeClass('is-loading');

		});

	});
});



