EasySocial.module('apps/fields/user/numeric/content', function($) {

var module = this;

EasySocial.Controller('Field.Numeric', {
	defaultOptions: {
		required: false,
		min: 0,
		max: 0,
		'{field}': '[data-field-numeric]',
		'{input}': '[data-field-numeric-input]',
		'{notice}': '[data-check-notice]'
	}
}, function(self, opts, base) { return {
	
	init: function() {
		opts.min = self.input().data('min');
		opts.max = self.input().data('max');
	},

	'{self} onRender': function() {
		var data = self.field().htmlData();
		opts.error = data.error || {};
	},

	'{input} mouseup': function() {
		self.validateInput();
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

		if (opts.required && $.isEmpty(value)) {
			self.raiseError(opts.error.required);
			return false;
		}

		if (!$.isEmpty(value) && opts.min > 0 && value < opts.min) {
			self.raiseError(opts.error.short);
			return false;
		}

		if (opts.max > 0 && value > opts.max) {
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