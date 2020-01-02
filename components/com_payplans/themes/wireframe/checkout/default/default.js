PayPlans.require()
.script('site/floatlabels')
.done(function($) {

	// use to keep the addon adding state
	var addonLock= false;

	var registerLink = $('[data-pp-register-link]');
	var loginLink = $('[data-pp-login-link]');
	var accountType = $('[data-pp-account-type]');

	<?php if ($accountType == 'register') { ?>
		$('[data-pp-login]').addClass('t-hidden');
		$('[data-pp-register]').removeClass('t-hidden');

		<?php $session = PP::session();
			if($this->config->get('registrationType') != 'auto' && (!$this->my->id && !$session->get('REGISTRATION_NEW_USER_ID', 0)
)) { ?>
			$('[data-pp-submit]').addClass('t-hidden');
		<?php } ?>
	<?php } ?>

	// Submit button
	$('[data-pp-submit]').on('click', function(event) {
		event.preventDefault();

		var form = $('[data-pp-checkout-form]');
		var required = false;

		form.find('[data-pp-form-group]').removeClass('has-error');
		form.find('[data-error-message]').addClass('t-hidden');

		// Check for required
		form.find(':input').each(function() {
			if ($(this).prop('required')) {
				var value = $(this).val();

				if (!value) {
					// Show required message
					var parent = $(this).parent('[data-pp-form-group]');

					parent.addClass('has-error');
					parent.find('[data-error-message]').removeClass('t-hidden');
					required = true;
				}
			}
		});

		if (required) {
			return;
		}

		var deferredObjects = [];

		form.trigger('onSubmit', [deferredObjects]);

		if (deferredObjects.length <= 0) {
			form.submit();
			return;
		}

		$.when.apply(null, deferredObjects)
			.done(function() {
				form.submit();
			})
			.fail(function() {
			});
	});

	// Login link
	loginLink.on('click', function() {

		$('[data-pp-register]').addClass('t-hidden');
		$('[data-pp-submit-register]').addClass('t-hidden');

		$('[data-pp-login]').removeClass('t-hidden');
		$('[data-pp-submit]').removeClass('t-hidden');
		$('[data-pp-submit-login]').removeClass('t-hidden');
		$('[data-pp-registration-wrapper]').removeClass('t-hidden');

		accountType.val('login');
	});

	// Registration link
	registerLink.on('click', function() {
		$('[data-pp-login]').addClass('t-hidden');
		$('[data-pp-register]').removeClass('t-hidden');
		
		<?php if($this->config->get('registrationType') != 'auto') { ?>
			$('[data-pp-submit]').addClass('t-hidden');
			$('[data-pp-registration-wrapper]').addClass('t-hidden');
		<?php } else { ?>
			$('[data-pp-submit-login]').addClass('t-hidden');
			$('[data-pp-submit-register]').removeClass('t-hidden');
		<?php } ?>

		accountType.val('register');
	});

	// Handle enter key for coupon code
	var couponInput = $('[data-pp-discount-code]');
	var applyCouponButton = $('[data-pp-discount-apply]');

	couponInput.on('keydown', function(event) {
		if (event.keyCode == 13) {
			event.preventDefault();
			applyCouponButton.click();
		}
	});

	// Apply discount codes
	applyCouponButton.on('click', function() {
		var button = $(this);
		var loader = $('[data-pp-checkout-loader]');
		var discountWrapper = $('[data-pp-discount-wrapper]');
		var discountMessage = $('[data-pp-discount-message]');
		var input = $('[data-pp-discount-code]');
		var coupon = input.val();
		var invoiceKey = $('[data-pp-invoice-key]').val();

		discountWrapper.removeClass('has-error');
		discountMessage.html('');

		PayPlans.ajax('site/views/discounts/check', {
			"code": coupon,
			"invoice_key": invoiceKey
		}).done(function(discount, totalHtml, total, isRecurring) {
			// remove all
			$('[data-pp-modifier-discount]').remove();

			$('[data-pp-modifiers]').prepend(discount);
			$('[data-pp-payable-label]').html(totalHtml);

			var hide = total <= 0.00 && !isRecurring;
			$('[data-pp-payment-form]').toggleClass('t-hidden', hide);

		}).fail(function(message) {
			discountWrapper.addClass('has-error');
			discountMessage.html(message);
		}).always(function() {
			button.removeClass('is-loading');
		});
	});

	// Business preferences
	var transactionPurpose = $('[data-pp-transaction-purpose]');

	transactionPurpose.on('change', function() {
		var element = $(this);
		var value = element.val();

		if (value == 'business') {
			$('[data-pp-business]').removeClass('t-hidden');
			return;
		}

		$('[data-pp-business]').addClass('t-hidden');
	});


	// Addons
	$('[data-addons-item]').on('click', function(ev) {
		var input = $(this);

		if (addonLock) {
			// do not let user click
			ev.preventDefault();
			return;
		}

		// to prevent user to click on other items before the current one finish.
		addonLock = true;

		var invoiceKey = $('[data-pp-invoice-key]').val();
		var addonId = input.val();
		var checked = input.is(':checked');
		var updateType = (checked) ? 'add' : 'remove';

		PayPlans.ajax('site/views/addons/updateCharges', {
			"plan_addons": addonId,
			"update_type": updateType,
			"invoice_key": invoiceKey
		}).done(function(html, totalHtml, total, isRecurring) {

			// remove all
			$('[data-pp-modifier-discount]').remove();

			// now repopulate with the udpates
			$('[data-pp-modifiers]').prepend(html);
			$('[data-pp-payable-label]').html(totalHtml);

			var hide = total <= 0.00 && !isRecurring;
			$('[data-pp-payment-form]').toggleClass('t-hidden', hide);

		}).fail(function(message) {

			PayPlans.dialog({
				"content": message
			});

		}).always(function() {
			addonLock = false;
		});
	});

	<?php if ($accountType == 'register') { ?>
		registerLink.trigger('click');
	<?php } ?>
});
