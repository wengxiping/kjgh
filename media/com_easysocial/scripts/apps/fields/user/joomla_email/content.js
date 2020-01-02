EasySocial.module('apps/fields/user/joomla_email/content', function($) {

var module = this;

EasySocial.Controller('Field.Joomla_email', {
	defaultOptions: {
		required: true,
		id: null,
		userid: null,
		reconfirm: false,
		event: null,
		registration: null,

		'{field}': '[data-field-joomla_email]',
		'{input}': '[data-field-email-input]',
		'{confirm}': '[data-field-email-reconfirm-input]',
		'{confirmFrame}': '[data-field-email-reconfirm-frame]'
	}
}, function(self, opts, base) { return {
	init: function() {
		self.origEmail = self.input().val();

		// Check for input value
		value = self.input().val();

		// Registration form should always display reconfirm field
		if (opts.reconfirm && !opts.registration && value) {
			self.confirmFrame().hide();
		}
	},

	"{self} onRender": function() {
		var data = self.field().htmlData();

		opts.error = data.error || {};
	},

	'{input} blur': function(el, ev) {
		var value = self.input().val();

		if (opts.reconfirm && value !== self.origEmail) {
			self.confirmFrame().show();
		}

		if (opts.reconfirm && value === self.origEmail && (opts.event === 'onEdit' || opts.event === 'onAdminEdit')) {
			self.confirmFrame().hide();
		}

		self.validateInput();
	},

	'{confirm} blur': function(el, ev) {
		self.validateInput();
	},

	validateInput: function() {
		self.clearError();

		var value = self.input().val();

		if($.isEmpty(value)) {
			if(!self.options.required) {
				return true;
			}

			self.raiseError(opts.error.required);
			return false;
		}

		if(self.options.reconfirm)
		{
			var reconfirm = self.confirm().val();

			if(value !== self.origEmail && $.isEmpty(reconfirm))
			{
				self.raiseError(opts.error.reconfirmrequired);
				return false;
			}

			if(!$.isEmpty(reconfirm) && value !== reconfirm)
			{
				self.raiseError(opts.error.mismatch);
				return false;
			}
		}

		return self.checkInput()
			.done(function() {
				self.clearError();
			})
			.fail(function(msg) {
				self.raiseError(msg);
			});
	},

	checkInput: function() {
		return EasySocial.ajax('fields/user/joomla_email/isValid', {
			id: self.options.id,
			userid: self.options.userid,
			email: self.input().val()
		});
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

		register.push(self.validateInput());
	}
}});

module.resolve();
});
