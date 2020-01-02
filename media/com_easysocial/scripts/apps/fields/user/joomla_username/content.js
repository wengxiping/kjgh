EasySocial.module('apps/fields/user/joomla_username/content', function($) {

var module = this;

EasySocial.Controller('Field.Joomla_username', {
	defaultOptions: {
		event: null,
		id: null,
		userid: null,

		'{button}': '[data-username-check]',
		'{input}': '[data-username-input]',
		'{available}': '[data-username-available]',
		"{error}": "[data-username-error]",
		"{errorMessage}": "[data-username-error] > [data-message]"
	}
}, function(self, opts, base) { return {
	state: false,

	init: function() {
	},

	'{button} click': function() {
		self.delayedCheck();
	},

	'{input} keyup': function() {
		self.delayedCheck();
	},

	delayedCheck: $.debounce(function() {
		self.checkUsername();
	}, 250),

	checkUsername: function() {
		self.clearError();

		var state = $.Deferred();
		var button = self.button();

		button.addClass('is-loading');

		var username = self.input().val();

		EasySocial.ajax('fields/user/joomla_username/isValid', {
			id: opts.id,
			userid: opts.userid,
			username: username,
			event: opts.event
		}).done(function(msg) {
			
			self.available().removeClass('t-hidden');
			self.error().addClass('t-hidden');
			state.resolve();

		}).fail(function(msg) {

			self.available().addClass('t-hidden');
			self.error().removeClass('t-hidden');
			self.errorMessage().html(msg);

			self.raiseError(msg);

			state.reject();

		}).always(function() {
			button.removeClass('is-loading');
		})

		return state;
	},

	raiseError: function(msg) {
		self.trigger('error', [msg]);
	},

	clearError: function() {
		self.trigger('clear');
	},

	'{self} onSubmit': function(el, ev, register, mode) {
		if (mode === 'onRegisterMini') {
			return;
		}

		register.push(self.checkUsername());
	}
}});

module.resolve();
});
