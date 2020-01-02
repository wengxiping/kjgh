EasySocial.module('site/followers/browser', function($) {

var module = this;

EasySocial.Controller('Followers.Browser', {
	defaultOptions: {
		"{wrapper}": "[data-followers-wrapper]",
		"{content}": "[data-followers-content]",
		"{items}": "[data-followers-item]"
	}
}, function(self, opts) { return {

	init: function() {
		opts.userId = self.element.data('id');
	},

	updateContents: function(contents) {
		self.content().replaceWith(contents);

		$('body').trigger('afterUpdatingContents');
	},

	getFollowers: function(type, callback) {
		self.wrapper().addClass('is-loading');

		EasySocial.ajax("site/controllers/followers/filter", {
			"id": opts.userId,
			"type": type
		}).done(function(contents) {
			self.updateContents(contents);

			self.wrapper().removeClass('is-loading');

			// trigger sidebar toggle for responsive view.
			self.trigger('onEasySocialFilterClick');
		}).always(function() {
			if (typeof(callback) == 'function') {
				callback.apply();
			}
		});
	}

}});

module.resolve();
});
