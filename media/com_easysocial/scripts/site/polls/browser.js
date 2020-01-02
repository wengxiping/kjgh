EasySocial.module('site/polls/browser', function($){

var module = this;

EasySocial.Controller('Polls.Browser', {
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

	getPolls: function(type, callback) {
		self.updatingContents();

		EasySocial.ajax('site/views/polls/filter', {
			"type": type,
			"clusterId": opts.clusterId,
			"clusterType": opts.clusterType,
			"userid": opts.userId
		}).done(function(html) {

			self.updateContents(html);

			if (typeof(callback) == 'function') {
				callback.apply(html);
			}

			// trigger sidebar toggle for responsive view.
			self.trigger('onEasySocialFilterClick');
		});
	}
}});

module.resolve();

});
