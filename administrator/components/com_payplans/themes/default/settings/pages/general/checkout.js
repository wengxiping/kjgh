PayPlans.ready(function($) {

	$('select[name=registrationType]').on('change', function() {
		var selected = $(this).val();

		$('[data-es-social]').toggleClass('t-hidden', selected != 'easysocial');
		$('[data-pp-auto]').toggleClass('t-hidden', selected != 'auto');
		$('[data-jom-social]').toggleClass('t-hidden', selected != 'jomsocial');

		var verification = $('select[name=account_verification]').val();
		var showAutologin = verification != 'auto' || selected != 'auto';

		$('[data-pp-autologin]').toggleClass('t-hidden', showAutologin);
	});

	$('select[name=account_verification]').on('change', function() {
		var selected = $(this).val();

		$('[data-pp-autologin]').toggleClass('t-hidden', selected != 'auto');
	});

	$('select[name=default_recaptcha_language]').on('change', function() {
		var selected = $(this).val();

		$('[data-recaptcha_language]').toggleClass('t-hidden', selected == 'auto');
	});
});