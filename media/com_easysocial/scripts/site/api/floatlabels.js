EasySocial.module('site/api/floatlabels', function($) {

	var module = this;

	var inputGroups = '.o-form-group';
	var inputSelectors = '.o-form-control.o-float-label__input:not(.is-static)';

	$(document).on('change.floatlabel', inputSelectors, function() {
		var self = $(this);
		var label = self.closest(inputGroups);

		label.addClass('is-focused');
	});

	$(document).on('focus.floatlabel', inputSelectors, function() {
		var self = $(this);
		var label = self.closest(inputGroups);

		label.addClass('is-focused');
	});


	$(document).on('blur.floatlabel', inputSelectors, function() {
		var self = $(this);
		var label = self.closest(inputGroups);
		var value = self.val();

		// When there is a value, we should inject is-filled.
		if ($.trim(value) !== '') {
			label.addClass('is-filled');
			label.removeClass('is-focused');
			return;
		}

		label.removeClass('is-filled');
		label.removeClass('is-focused');
	});


	$(inputSelectors).trigger('blur.floatlabel');

	module.resolve();
});