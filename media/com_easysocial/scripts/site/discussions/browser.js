EasySocial.module('site/discussions/browser', function($){

var module = this;

EasySocial.Controller('Discussions.Browser', {
	defaultOptions: {
		clusterType: false,
		clusterId: false,
		"{contents}": "[data-contents]",
		"{wrapper}": "[data-wrapper]",
		"{result}": "[data-result]"
	}
}, function(self,opts,base) { return {

	updatingContents: function() {
		self.wrapper().empty();
		self.contents().addClass('is-loading');
	},

	updateContents: function(html) {
		self.contents().removeClass('is-loading');
		self.wrapper().replaceWith(html);
	},

	getDiscussions: function(type, callback) {

		self.updatingContents();

		EasySocial.ajax('site/controllers/discussions/getDiscussions', {
			"id": opts.clusterId,
			"filter": type
		}).done(function(contents) {

			self.updateContents(contents);

			if (typeof(callback) == 'function') {
				callback.apply(contents);
			}

			// trigger sidebar toggle for responsive view.
			self.trigger('onEasySocialFilterClick');
		});
	}
}});

module.resolve();

});
