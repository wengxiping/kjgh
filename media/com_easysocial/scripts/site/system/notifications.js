EasySocial.module('site/system/notifications', function($){

var module = this;

EasySocial.Controller('System.Notifications', {
	defaultOptions: {
		"interval": 2,
		userId: null
	}
}, function(self, opts) { return {
	init: function(){
		self.start();
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

	check: function() {
		// When checking, ensure that all previous queues are stopped
		self.stop();

		// Needs to run in a loop since we need to keep checking for new notification items.
		setTimeout(function(){

			$.ajax({
				"url": window.es.rootUrl + '/components/com_easysocial/polling.php',
				"method": "post",
				"data": {
					"method": "notifier",
					"userId": opts.userId
				}
			}).done(function(data) {
				self.element.trigger('notification.updates', data);
				self.start();
			});

		}, self.getInterval());
	}

}});

module.resolve();
});
