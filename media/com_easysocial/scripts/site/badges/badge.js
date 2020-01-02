EasySocial.module('site/badges/badge', function($) {
	var module = this;

	EasySocial.Controller('Badges.Badge', {
		defaultOptions: {
			id					: 0,
			total				: 0,

			'{achieversList}'	: '[data-badge-achievers-list]',

			'{achiever}'		: '[data-badge-achievers-achiever]',

			'{loadButton}'		: '[data-badge-achievers-load]',
		}
	}, function(self) {
		return {
			init: function() {
				self.options.id = self.element.data('id');
				self.options.total = self.element.data('total-achievers');
			},

			'{loadButton} click': function(el) {
				var current = el.data('nextlimit');

				if (el.enabled()) {
					el.disabled(true);

					el.hide();

					EasySocial.ajax('site/controllers/badges/loadAchievers', {
						id: self.options.id,
						start: current
					}).done(function(html, nextlimit) {

						self.achieversList().append(html);

						el.enabled(true);

						if(nextlimit > 0) {
							el.data('nextlimit', nextlimit)
							el.show();
						}

					}).fail(function(msg) {

					});
				}
			}
		}
	});

	module.resolve();
});
