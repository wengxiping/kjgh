EasySocial.module('apps/fields/user/joomla_password/content', function($) {

var module = this;

EasySocial.require()
.library('passwordstrength')
.done(function(){

EasySocial.Controller('Field.Joomla_password', {
	defaultOptions: {
		event               : null,
		triggerError        : true,

		required            : false,
		passwordStrength    : false,
		reconfirmPassword   : false,
		requireOriginal     : false,

		min : 4,
		max : 0,

		minInteger: 0,
		minSymbol: 0,
		minUpperCase: 0,

		'{field}': '[data-field-joomla_password]',
		'{original}'    : '[data-field-password-orig]',
		'{input}'       : '[data-field-password-input]',
		'{reconfirm}'   : '[data-field-password-confirm]',
		'{pwResetSubmitButton}' : '[data-password-reset-submit]',
		
		'{warning}'    : '[data-field-password-warning]',
		'{reconfirmNotice}' : '[data-reconfirmPassword-failed]',

		// Password strength
		'{strength}': '[data-password-strength]'
	}
}, function(self, opts, base) { return {
	
	init: function() {
		if (opts.passwordStrength) {
			self.initPasswordStrength();
		}
	},

	"{self} onRender": function() {
		var data = self.field().htmlData();

		opts.error = data.error;
	},

	initPasswordStrength: function() {

		self.input().password_strength({
			container: self.strength.selector,
			minLength: opts.min,
			texts: {
				1: self.strength().data('message-1'),
				2: self.strength().data('message-2'),
				3: self.strength().data('message-3'),
				4: self.strength().data('message-4'),
				5: self.strength().data('message-5')
			},
			onCheck: function(level) {
				if(level <= 1) {
					self.strength()
						.removeClass('t-text--warning')
						.removeClass('t-text--success')
						.addClass('t-text--danger t-fs--sm');
				}

				if(level > 1 && level <= 3) {
					self.strength()
						.removeClass('t-text--danger')
						.removeClass('t-text--success')
						.addClass('t-text--warning t-fs--sm');
				}

				if(level >= 4) {
					self.strength()
						.removeClass('t-text--danger')
						.removeClass('t-text--warning')
						.addClass('t-text--success t-fs--sm');
				}
			}
		})
	},

	'{input} keyup': function() {
		self.validatePassword();
	},

	'{input} blur': function() {
		self.validatePassword();
	},

	'{reconfirm} keyup': function() {
		self.validatePassword();
	},

	'{reconfirm} blur': function() {
		self.validatePassword();
	},

	validatePassword: function() {
		self.clearError();

		var input = self.input().val();
		var reconfirm = self.reconfirm().val();

		if(self.options.event === 'onRegister' && !self.validatePasswordInput() ) {
			return false;
		}

		if(self.options.event === 'onEdit' && !self.validatePasswordEdit()) {
			return false;
		}

		if(self.options.reconfirmPassword && !self.validatePasswordConfirm()) {
			return false;
		}

		return true;
	},

	validatePasswordInput: function() {
		var input = self.input().val();

		if (!opts.error) {
			var data = self.field().htmlData();

			opts.error = data.error;
		}

		if($.isEmpty(input)) {
			self.raiseError(opts.error.empty);
			return false;
		}

		if(self.options.min > 0 && input.length < self.options.min) {
			self.raiseError(opts.error.min);
			return false;
		}

		if(self.options.max > 0 && input.length > self.options.max) {
			self.raiseError(opts.error.max);
			return false;
		}

		if(self.options.minInteger > 0) {
			var test = input.match(/[0-9]/g);
			if (!test || test.length < self.options.minInteger) {
				self.raiseError(opts.error.mininteger);
				return false;
			}
		}

		if(self.options.minSymbol > 0) {
			var test = input.match(/[\W]/g);
			if (!test || test.length < self.options.minSymbol) {
				self.raiseError(opts.error.minsymbols);
				return false;
			}
		}

		if(self.options.minUpperCase > 0) {
			var test = input.match(/[A-Z]/g);
			if (!test || test.length < self.options.minUpperCase) {
				self.raiseError(opts.error.minupper);
				return false;
			}
		}


		return true;
	},

	validatePasswordEdit: function() {
		var orig = self.original().val(),
			input = self.input().val();

		// If both original and input is empty, then we return true as it is not mandatory in edit
		if($.isEmpty(input) && $.isEmpty(orig)) {
			return true;
		}

		// Only original is empty
		if($.isEmpty(orig) && self.options.requireOriginal) {
			self.raiseError(opts.error.emptyoriginal);
			return false;
		}

		// Original is not empty, then we validate the new password
		return self.validatePasswordInput();
	},

	validatePasswordConfirm: function() {
		var input = self.input().val();
		var reconfirm = self.reconfirm().val();

		// Check if either input or reconfirm is not empty
		if (!$.isEmpty(input) || !$.isEmpty(reconfirm)) {
			
			if($.isEmpty(input)) {
				self.raiseError(opts.error.empty);
				return false;
			}

			if($.isEmpty(reconfirm)) {
				self.raiseError(opts.error.emptyconfirm);
				return false;
			}

			if(input !== reconfirm) {
				self.raiseError(opts.error.mismatch);
				return false;
			}
		}

		return true;
	},

	raiseError: function(msg) {
		if (self.options.triggerError) {
			self.trigger('error', [msg]);
		} else {
			self.warning().show();
			self.warning().text(msg);
		}
	},

	clearError: function() {
		if (self.options.triggerError) {
			self.trigger('clear');
		} else {
			self.warning().hide();
			self.warning().text('');
		}
	},

	"{pwResetSubmitButton} click": function() {
		return self.validatePassword();
	},

	"{self} onSubmit": function(el, event, register, mode) {

		if (mode === 'onRegisterMini') {
			return;
		}

		register.push(self.validatePassword());
	}
}});

module.resolve();
});
});
