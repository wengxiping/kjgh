EasySocial.module('site/system/broadcast', function($){

var module = this;

EasySocial.require()
.script('site/vendors/gritter')
.done(function($){

EasySocial.Controller('System.Broadcast', {
	defaultOptions: {
		interval: 30,
		sticky: false,
		period: 8
	}
}, function(self, opts, base) { return {

	init: function() {
		self.startMonitoring();
	},

	startMonitoring: function() {
		var interval = opts.interval * 1000;

		opts.state = setTimeout(self.check, interval);
	},

	stopMonitoring: function() {
		clearTimeout(opts.state);
	},

	check: function() {
		// Stop monitoring so that there wont be double calls at once.
		self.stopMonitoring();

		var interval = opts.interval * 1000;

		// Needs to run in a loop since we need to keep checking for new notification items.
		setTimeout(function(){

			EasySocial.ajax('site/controllers/notifications/getBroadcasts')
				.done(function(items){

					if (items) {

						$(items).each(function(i, item) {

							var data = {
								title: item.title,
								raw_title: item.raw_title,
								text: item.content,
								image: item.authorAvatar,
								sticky: self.options.sticky,
								time: self.options.period * 1000,
								class_name: 'es-broadcast'
							};

							$.gritter.add(data);

							$('body').trigger('update.notifications', ['broadcast', {"title": data.raw_title, "contents": data.text, "link": data.link, "image": data.image}]);
						});
					}

					// Continue monitoring.
					self.startMonitoring();
				});

		}, interval);

	}
}});

module.resolve();
});

});
