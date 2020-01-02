PayPlans.require()
.script('site/card')
.done(function($) {
	var wrapper = $('[data-card-<?php echo $uuid;?>]');

	// Initialize card js
	wrapper.CardJs();

	var cardInput = wrapper.find('input[name=<?php echo $inputNames->card;?>]');
	var codeInput = wrapper.find('input[name=<?php echo $inputNames->code;?>]');
	var expMonth = wrapper.find('input[name=<?php echo $inputNames->expireMonth;?>]');
	var expYear = wrapper.find('input[name=<?php echo $inputNames->expireYear;?>]');

	<?php if ($inputNames->name) { ?>
	<?php } ?>

	wrapper.CardJs('refresh');
	
	// cardInput.trigger('paste');
	// codeInput.trigger('paste');
});