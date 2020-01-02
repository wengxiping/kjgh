EasySocial.module('site/toolbar/friends', function($) {

var module = this;

EasySocial.Controller('Notifications.Friends.Popbox', {
	
	defaultOptions: {
		// This should be added by the implementer
		'messages' : {
			'rejected': ''
		},

		'{actions}': '[data-actions]',
		'{item}': '[data-item]',
		"{reject}": "[data-action=reject]",
		"{accept}": "[data-action=accept]",

		actions : []
	}
}, function(self, opts) { return {
	
	getToolbarController: function() {
		var element = $('[data-notifications][data-type="friends"]');

		return element;
	},

	getRequesterId: function(element) {
		var item = element.closest(self.item.selector);
		var id = item.data('id');

		return id;
	},

	updateCounter: function() {
		var controller = self.getToolbarController();
		var counter = controller.find('[data-counter]').first().text();
		counter = parseInt(counter) - 1;

		if (counter <= 0) {
			controller.removeClass('has-new');
		}

		// Update the count
		controller.find('[data-counter]').html(counter.toString());
	},

	insertMessage: function(button, message) {
		// Get the actions wrapper
		var wrapper = button.parents(self.actions.selector);

		wrapper.html(message)
			.addClass('t-text--muted');
	},

	'{reject} click': function(button, event) {
		// Stop other events from propagating
		event.preventDefault();
		event.stopPropagation();

		if (opts.actions['reject']) {
			return;
		}

		button.addClass('is-loading');

		opts.actions['reject'] = true;

		var id = self.getRequesterId(button);
		
		// Update the message
		self.insertMessage(button, opts.messages.rejected);

		// Update the toolbar's counter
		self.updateCounter();

		EasySocial.ajax('site/controllers/friends/reject', {
			"id": id
		}).always(function() {
			opts.actions['reject'] = false;
			button.removeClass('is-loading');
		});
	},

	'{accept} click': function(button, event) {

		// Stop other events from propagating
		event.preventDefault();
		event.stopPropagation();

		if (opts.actions['accept']) {
			return;
		}

		button.addClass('is-loading');

		opts.actions['accept'] = true;

		var id = self.getRequesterId(button);

		// Update the counter
		self.updateCounter();

		// Approve the friend request
		EasySocial.ajax('site/controllers/friends/approve', {
			"id": id
		}).done(function(newButton, message) {
			self.insertMessage(button, message);
		}).always(function() {
			opts.actions['accept'] = false;
			button.removeClass('is-loading');
		});
	}
}});


module.resolve();

});
