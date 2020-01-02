EasySocial.module('apps/fields/user/textbox/content', function($) {

var module = this;

EasySocial.Controller('Field.Textbox', {
	defaultOptions: {
		required: false,
		min: 0,
		max: 0,
		'{field}': '[data-field-textbox]',
		'{input}': '[data-field-textbox-input]',
		'{notice}': '[data-check-notice]'
	}
}, function(self, opts, base) { return {
	
	init: function() {
		opts.min = self.field().data('min');
		opts.max = self.field().data('max');
	},

	'{self} onRender': function() {
		var data = self.field().htmlData();
		opts.error = data.error || {};
	},

	'{input} keyup': function() {
		self.validateInput();
	},

	'{input} blur': function() {
		self.validateInput();
	},

	validateInput: function() {
		self.clearError();

		var value = self.input().val();

		if (self.options.required && $.isEmpty(value)) {
			self.raiseError(opts.error.required);
			return false;
		}

		if (!$.isEmpty(value) && self.options.min > 0 && value.length < self.options.min) {
			self.raiseError(opts.error.short);
			return false;
		}

		if (self.options.max > 0 && value.length > self.options.max) {
			self.raiseError(opts.error.long);
			return false;
		}

		return true;
	},

	raiseError: function(msg) {
		self.trigger('error', [msg]);
	},

	clearError: function() {
		self.trigger('clear');
	},

	'{self} onError': function(el, ev, type) {
		if (type === 'required') {
			self.raiseError(opts.error.required);
		}

		if (type === 'validate') {
			self.raiseError(opts.error.invalid);
		}
	},

	'{self} onSubmit': function(el, ev, register) {
		register.push(self.validateInput());
	}
}});

module.resolve();
});