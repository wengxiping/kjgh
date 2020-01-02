EasySocial.module('apps/fields/user/address/content', function($) {

var module = this;

EasySocial.Controller('Field.Address', {
	defaultOptions: {
		required: {},
		show: {},

		"{field}": "[data-field-address]",
		"{address1}": "[data-field-address-address1]",
		"{address2}": "[data-field-address-address2]",
		"{city}": "[data-field-address-city]",
		"{state}": "[data-field-address-state]",
		"{country}": "[data-field-address-country]",
		"{zip}": "[data-field-address-zip]",
		'{required}': '[data-required]',
		'{notice}': '[data-check-notice]'
	}
}, function(self, opts, base) { return {

	init : function() {
	},

	fields: [
		'address1',
		'address2',
		'city',
		'state',
		'zip',
		'country'
	],

	"{self} onRender": function(element, event) {
		var data = self.field().htmlData();

		opts.error = data.error;
	},

	validateInput : function() {
		self.clearError();

		var errorRaised = false;

		self.clearError();

		$.each(self.fields, function(i, field) {
			var el = self[field]();

			el.removeClass('has-error');

			var val = el.val();

			if ($.isEmpty(val) && self.options.required[field] && self.options.show[field]) {
				el.addClass('has-error');

				if (!errorRaised) {
					self.raiseError(opts.error[field]);
					errorRaised = true;
				}
			}
		});

		if (errorRaised) {
			return false;
		}

		return true;
	},

	'{address1}, {address2}, {zip}, {city}, {state} blur': function() {
		self.validateInput();
	},

	'{country} change': function(el) {
		self.validateInput();

		if (self.state().is('select')) {

			if (el.val() == '') {
				self.state().empty();
				var option = $('<option></option>').html(self.options.selectCountryText).val('').appendTo(self.state());

				return;
			}

			EasySocial.ajax('fields/user/address/getStates', {
				id: self.options.id,
				country: el.val()
			}).done(function(states) {
				self.state().empty();
				$('<option></option>').html(self.options.selectStateText).val('').appendTo(self.state());

				$.each(states, function(code, name) {
					var option = $('<option></option>').html(name).val(name).appendTo(self.state());
				});
			});
		}
	},

	raiseError: function(message) {
		self.trigger('error', [message]);

		// self.notice()
		//     .css('color', '#a94442')
		//     .text(msg)
		//     .parent('.controls-error')
		//     .show();
	},

	clearError: function() {
		self.trigger('clear');
	},

	"{self} onSubmit" : function(el, event, register) {
		register.push(self.validateInput());
	},

	"{self} onConfigChange": function(el, event, name, value) {
		var requires = ['address1', 'address2', 'city', 'zip', 'state', 'country'];

		if($.inArray(name, requires) >= 0) {
			self.options.required[name] = !!value;
		}

		self.required().hide();

		$.each(requires, function(i, t) {
			if(self.options[t]) {
				self.required().show();
				return false;
			}
		});
	}
}});

module.resolve();
});
