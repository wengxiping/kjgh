EasySocial.module('site/toolbar/notifications' , function($){

var module = this;

EasySocial.Controller('Notifications', {
	defaultOptions: {
		"interval": 45,
		"{friends}": "[data-notifications][data-type=friends]",
		"{conversations}": "[data-notifications][data-type=conversations]",
		"{system}": "[data-notifications][data-type=system]",
		"{item}": "[data-toolbar-item]"
	}
}, function(self, opts){ return {

	init: function() {

		// Initialize responsive layout for the notification bar.
		self.setLayout();

		$(window).on('notification.updates', function(event, data) {

			if (data) {
				if (data.conversation != undefined && data.conversation.total > 0) {
					var total = data.conversation.total > 99 ? '99+' : data.conversation.total;
					var counter = self.conversations().find("[data-counter]");
					var previous = counter.text();

					//update counter
					counter.text(total);
					self.conversations().addClass('has-new');

					// Trigger for 3rd party to intercept
					if (previous != total && total > 0) {
						$('body').trigger('update.notifications', ['conversation', data.conversation.data]);
					}
				} else {
					self.conversations().removeClass('has-new');
				}

				if (data.friend != undefined && data.friend.total > 0) {
					var total = data.friend.total > 99 ? '99+' : data.friend.total;

					self.friends().find("[data-counter]").text(total);
					self.friends().addClass('has-new');
				} else {
					self.friends().removeClass('has-new');

				}

				if (data.system != undefined && data.system.total > 0) {
					var total = data.system.total > 99 ? '99+' : data.system.total;
					self.system().find("[data-counter]").text(total);
					self.system().addClass('has-new');
				} else {
					self.system().removeClass('has-new');
				}
			}

		});


	},

	"{window} resize": $.debounce(function(){
		self.setLayout();
	}, 250),

	setLayout: function() {

		var elem = self.element;
		var toolbarWidth = elem.outerWidth(true) - 80;
		var allItemWidth = 0;

		// Calculate how much width toolbar items are taking
		self.item().each(function(){
			allItemWidth += $(this).outerWidth(true);
		});

		var exceeded = (allItemWidth > toolbarWidth);

		elem.toggleClass("narrow", exceeded).toggleClass("wide", !exceeded);
	},

	getInterval: function() {
		var interval = opts.interval * 1000;
		return interval;
	},

	'{window} easysocial.clearSystemNotification': function() {
		// clear system notification.
		self.system().removeClass('has-new');
		self.system().find("[data-counter]").text(0);
	},

}});

module.resolve();

});
