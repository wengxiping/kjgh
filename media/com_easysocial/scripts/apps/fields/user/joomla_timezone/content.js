EasySocial.module('apps/fields/user/joomla_timezone/content', function($) {

var module = this;

EasySocial
.require()
.library('chosen')
.done(function($) {

EasySocial.Controller('Field.Joomla_timezone', {
	defaultOptions: {
		"required": false,
		'{field}': '[data-field-joomla_timezone]',
		'{input}': '[data-field-joomla_timezone-input]'
	}
}, function(self, opts, base) { return {

	init : function() {
		opts.message = self.element.find('[data-error]').data('error');

		self.input().chosen({
			// width: $('.o-control-input').eq(0).width() + 'px', // #3003
			width: '100%',
			allow_single_deselect: true,
			search_contains: true
		});
	},

	validateInput: function() {
		if(!self.options.required) {
			return true;
		}

		self.clearError();

		var value = self.input().val();

		if(value === 'null' || $.isEmpty(value)) {
			self.raiseError();
			return false;
		}

		return true;
	},

	raiseError: function() {
		self.trigger('error', opts.message);
	},

	clearError: function() {
		self.trigger('clear');
	},

	'{input} change': function() {
		self.validateInput();
	},

	"{self} onSubmit": function(el, event, register) {
		register.push(self.validateInput());
	}

}});

module.resolve();
});
});
