EasySocial.module('apps/fields/user/currency/content', function($) {
var module = this;

EasySocial.Controller('Field.Currency', {
	defaultOptions: {
		required: false,
		'{field}': '[data-field-currency]',
		'{dollarInput}': '[data-currency-dollar]',
		'{centInput}': '[data-currency-cent]'
	}
}, function(self, opts, base) { return {

	init: function() {
		console.log('init');
	},

	'{self} onRender': function() {
		var data = self.field().htmlData();
		opts.error = data.error || {};
	},

	'{dollarInput} keyup': function() {
		self.validateInput();
	},

	'{dollarInput} blur': function() {
		self.validateInput();
	},

	'{centInput} keyup': function() {
		self.validateInput();
	},

	'{centInput} blur': function() {
		self.validateInput();
	},

	validateInput: function() {
		self.clearError();

		var dollar = self.dollarInput().val();
		var cent = self.centInput().val()

		if (self.options.required && ($.isEmpty(dollar) || $.isEmpty(cent))) {
			self.raiseError(opts.error.required);
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


	'{self} onSubmit': function(el, ev, register) {
		register.push(self.validateInput());
	}
}});

module.resolve();
});
