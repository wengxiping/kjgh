PayPlans.ready(function($) {

var apply = $('[data-pp-referral-apply]');
var input = $('[data-pp-referral-code]');
var message = $('[data-pp-referral-message]');
var wrapper = $('[data-pp-referral-wrapper]');

apply.on('click', function() {
	var code = input.val();

	message.html('');
	apply.addClass('is-loading');
	wrapper.removeClass('has-error');
	
	PayPlans.ajax('site/views/referrals/apply', {
		"invoice_key": "<?php echo $invoice->getKey();?>",
		"code": code
	})
	.done(function() {
		window.location.reload();
	})
	.fail(function(str) {
		message.html(str);
		wrapper.addClass('has-error');
	})
	.always(function() {
		apply.removeClass('is-loading');
	});
});



});