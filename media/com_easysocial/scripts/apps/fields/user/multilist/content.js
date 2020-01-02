EasySocial.module('apps/fields/user/multilist/content', function($) {

var module = this;

EasySocial.Controller('Field.Multilist', {
	defaultOptions: {
		required        : null,
		multiple        : null,

		"{field}"       : "[data-field-multilist]",

		"{item}"        : "[data-field-multilist-item]",

		"{option}"      : "[data-field-multilist-item] option"
	}
}, function(self, opts, base) { return {

	"{self} onRender": function() {
		var data = self.field().htmlData();

		opts.error = data.error || {};
	},

	validateInput : function() {
		self.clearError();

		if(self.options.multiple && self.item().children(':selected').length <= 0) {
			self.raiseError();
			return false;
		}

		// The only way to test for an empty value is when the value is empty and it's required.
		if(self.item().children(':selected' ).val() == '') {
			self.raiseError();
			return false;
		}

		return true;
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
		if(value) {
			self.option().eq(index).attr('selected', 'selected');
		} else {
			self.option().eq(index).removeAttr('selected');
		}
	}
}});

module.resolve();
});