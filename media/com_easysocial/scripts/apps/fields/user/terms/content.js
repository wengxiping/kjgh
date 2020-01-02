EasySocial.module('apps/fields/user/terms/content', function($) {

var module = this;

EasySocial.Controller('Field.Terms', {
	defaultOptions: {
		event: null,
		required: false,

		'{textbox}': '[data-field-terms-textbox]',
		'{checkbox}': '[data-field-terms-checkbox]'
	}
}, function(self, opts, base) { return {

	"{self} onRender": function() {
		var data = self.element.htmlData();

		opts.error = data.error || {};
	},

	validateInput: function() {
		self.clearError();

		// We should not prevent admin from hitting errors
		if (opts.event == 'onAdminEdit') {
			return true;
		}

		if (opts.required && !self.checkbox().is(':checked')) {
			self.raiseError(opts.error.required);
			return false;
		}

		return true;
	},

	'{checkbox} change': function() {
		self.validateInput();
	},

	raiseError: function() {
		self.trigger('error', [opts.error.required]);
	},

	clearError: function() {
		self.trigger('clear');
	},

	'{self} onSubmit': function(el, event, register) {
		register.push(self.validateInput());
	}
}});

module.resolve();
});
