EasySocial.module('apps/fields/user/dropdown/content', function($) {
var module = this;

EasySocial.Controller('Field.Dropdown', {
	defaultOptions: {
		required: null,
		"{field}": "[data-field-dropdown]",
		"{item}": "[data-field-dropdown-item]",
		"{option}": "[data-field-dropdown-item] option"
	}
}, function(self, opts, base) { return {

	validateInput : function() {
		self.clearError();

		if(self.options.required && $.isEmpty(self.item().val())) {
			self.raiseError();
			return false;
		}

		return true;
	},

	"{self} onRender": function() {
		var data = self.field().htmlData();
		opts.error = data.error || {};
	},

	raiseError: function() {
		self.trigger('error', [opts.error.empty]);
	},

	clearError: function() {
		self.trigger('clear');
	},

	"{self} onSubmit": function(el, event, register) {
		// If field is not required, skip the checks.

		if(!self.options.required)
		{
			register.push(true);
			return;
		}

		register.push(self.validateInput());

		return;
	},

	'{self} onChoiceAdded': function(el, event, index) {
		if(self.option().eq(index).length > 0) {
			self.option().eq(index).before($('<option></option>'));
		} else {
			self.item().append($('<option></option>'));
		}
	},

	'{self} onChoiceValueChanged': function(el, event, index, value) {
		self.option().eq(index).val(value);
	},

	'{self} onChoiceTitleChanged': function(el, event, index, value) {
		self.option().eq(index).text(value);
	},

	'{self} onChoiceRemoved': function(el, event, index) {
		self.option().eq(index).remove();
	},

	'{self} onChoiceToggleDefault': function(el, event, index, value) {
		self.option().removeAttr('selected');

		if(value) {
			self.option().eq(index).attr('selected', 'selected');
		}
	}
}});

module.resolve();
});
