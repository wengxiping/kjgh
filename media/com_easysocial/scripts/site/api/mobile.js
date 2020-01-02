EasySocial.module('site/api/mobile', function($) {

var module = this;

if (window.es.mobile) {

	// Only process this when all DOM is laoded. #2789
	$('document').ready(function() {

		// Fix scrolling issue when mobile actions are rendered
		$('body').on('shown.bs.dropdown', function(event) {
			$('body').addClass('t-body-overflow-hidden');

			var target = $(event.target);
			var header = target.parents().hasClass('es-profile-header');

			if (header) {
				$('body').addClass('dropdown-mobile-from-header');
				return;
			}

			$('body').addClass('dropdown-mobile-from-container');
		});

		// Fix scrolling issue when mobile actions are rendered
		$('body').on('hide.bs.dropdown hidden.bs.dropdown', function() {
			$('body').removeClass('t-body-overflow-hidden dropdown-mobile-from-header dropdown-mobile-from-container');
		});

		// Prevent clicking on ul to close
		var dropdownButton = $('[data-bs-toggle=dropdown]');
		var dropdown = dropdownButton.siblings('.dropdown-menu');

		dropdown.on('click touchstart', function(event) {
			var target = $(event.target);

			if (target.is(dropdown)) {
				event.preventDefault();
				event.stopPropagation();
			}

			// User tapped on a link on the actions section
			if (target.is('a')) {

				if (target.attr('href') == 'javascript:void(0);') {
					return;
				}

				event.stopPropagation();

				var listItem = target.parent();
				listItem.addClass('is-loading');

				return;
			}
		});
	})
}

module.resolve();
});
