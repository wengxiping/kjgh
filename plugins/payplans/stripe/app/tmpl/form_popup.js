PayPlans.ready(function($) {

	var form = $('[data-pp-stripe-form]');
	var checkout = StripeCheckout.configure({
		key: '<?php echo $publicKey;?>',
		currency: '<?php echo $currency; ?>',

		token: function(response) {

			if (response.error) {
				console.log(response.error);
				return;
			}

			submission = true;

			form.find('[data-stripe-token]')
				.val(response.id);

			form.submit();
		}
	});

	// Render the popup
	var submission = false;

	checkout.open({
		name: '<?php echo $this->html('string.escape', $storeName);?>',
		<?php if ($populateEmail) { ?>
		email: '<?php echo $this->html('string.escape', $this->my->email);?>',
		<?php } ?>

		closed: function() {
			if (!submission) {
				var redirect = "<?php echo PPR::_("index.php?option=com_payplans&view=payment&task=complete&action=cancel&payment_key=" . $payment->getKey() . '&tmpl=component');?>";
				
				window.location = redirect;

				return;
			}

			form.submit();
		},

		amount: "<?php echo $amount;?>"
	});
});