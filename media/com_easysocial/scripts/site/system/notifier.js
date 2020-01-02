EasySocial.module('site/system/notifier', function($){

var module = this;

EasySocial.Controller('System.Notifier', {
	defaultOptions: {
		"interval": 30
	}
}, function(self, opts) { return {

	init: function() {

		if (!opts.guest) {
			self.start();
		}
	},

	getInterval: function() {
		return opts.interval * 1000;
	},

	start: function() {
		opts.state = setTimeout(self.check, self.getInterval());
	},

	stop: function() {
		clearTimeout(opts.state);
	},

	execute: function(forceDisplay) {
		// before we send request to server, lets gather data from
		// other plugins.
		var collection = {};
		self.element.trigger('notifier.collection', collection);

		EasySocial.ajax('site/controllers/notifier/check', {
			"data": collection,
		}).done(function(data) {

			data.forceDisplay = forceDisplay;

			self.element.trigger('notifier.updates', data);
			self.start();
		});
	},

	check: function(executeNow, forceDisplay) {

		forceDisplay = forceDisplay == undefined ? false : forceDisplay;
		executeNow = executeNow == undefined ? false : executeNow;
		
		// When checking, ensure that all previous queues are stopped
		self.stop();

		// Needs to run in a loop since we need to keep checking for new notification items.
		if (executeNow) {
			self.execute(forceDisplay);
			return;
		}

		setTimeout(function(){
			self.execute(forceDisplay);
		}, self.getInterval());
	}

}});

module.resolve();
});
