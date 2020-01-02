EasySocial.module('apps/fields/user/url/content', function($) {

var module = this;

EasySocial.Controller('Field.Url', {
	defaultOptions: {
		required: false,

		'{field}': '[data-field-url]',
		'{input}': '[data-field-url-input]'
	}
}, function(self, opts, base) { return {

	init: function() {
	},
	
	"{self} onRender": function() {
		var data = self.field().htmlData();

		opts.error = data.error || {};
	},

	'{input} blur': function() {
		self.validateInput();
	},

	'{input} keyup': function() {
		self.validateInput();
	},

	validateInput: function() {
		self.clearError();

		var value = self.input().val();

		if (self.options.required && $.isEmpty(value)) {
			self.raiseError(opts.error.empty);
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

	'{self} onError': function(el, event, type, field) {
		self.raiseError(opts.error.empty);
	},

	'{self} onSubmit': function(el, ev, register) {
		register.push(self.validateInput());
	}
}});

module.resolve();

});