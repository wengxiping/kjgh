EasySocial.module( 'site/toolbar/system' , function($){

var module = this;

EasySocial.Controller('Notifications.System.Popbox', {
	defaultOptions: {
		"{readAll}": "[data-readall]",
		"{items}": "[data-items]",
		"{empty}": "[data-empty]"
	}
}, function(self) { return {

	getToolbarController: function() {
		var element = $('[data-notifications][data-type="system"]');

		return element.controller();
	},

	updateCounter: function() {
		var controller = self.getToolbarController();

		// Update the counter and states
		controller.element.removeClass('has-new');
		controller.counter().html(0);
	},

	"{readAll} click": function(link, event) {

		EasySocial.ajax( 'site/controllers/notifications/setAllRead', {
			"state": "read"
		}).done(function() {

			// Remove the items
			self.items().remove();
			self.empty().parent().addClass('is-empty');
			self.empty().removeClass('t-hidden');

			$(window).trigger('easysocial.clearSystemNotification');
		});
	}
}});

module.resolve();

});
