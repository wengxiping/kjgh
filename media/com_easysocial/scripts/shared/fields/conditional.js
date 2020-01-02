EasySocial.module('shared/fields/conditional', function($) {

var module = this;
var isController = $.isController('EasySocial.Controller.Field.Conditional');

if (isController) {
	return;
}

EasySocial.Controller('Field.Conditional', {
	defaultOptions: {
		id: null,
		fieldTargeted: [],
		operator: [],
		conditionValue: [],
		result: []
	}
}, function(self, opts, base) { return {

	init: function() {

		opts.id = base.data('id');
		var isConditional = base.data('isconditional');

		if (isConditional) {
			self.bindTargetedFields();
		}
	},

	bindTargetedFields: function() {
		var conditions = base.data('conditions');

		if (conditions) {
			$.each(conditions, function(key, value) {
				opts.fieldTargeted.push(value.fieldId);
				opts.operator.push(value.operator);
				opts.conditionValue.push(value.value);

				// Properly format the conditon value
				self.formatConditionsValue(key);

				// Bind listen event on targeted field
				self.bindFields(key);
			})
		}
	},

	bindFields: function(key) {

		var field = self.getTargetedField(key);

		if (self.isAutocomplete(key)) {
			self.bindAutocompleteFields(key);
		} else {

			// Listen on change event
			field.on('change', function() {
				self.fieldChanged(key);
			});

			// Listen on keyup event
			field.on('keyup', function() {
				self.fieldChanged(key);
			});

			// Re-check the current field value and determine if we should show or not the field
			field.on('onFieldShow', function() {
				self.fieldChanged(key);
			});

			// Directly trigger the checking during initial page load
			// only if the targeted field is not hidden
			if (!field.hasClass('t-hidden')) {
				self.fieldChanged(key);
			}
		}

		// Hide other field that are dependent on the field that are being hide
		field.on('onFieldHide', function() {
			self.hideSelf(key);
		});
	},

	bindAutocompleteFields: function(key) {

		var field = self.getTargetedField(key);
		var autocomplete = field.find('[data-field-suggest]');
		var accumulativeValue = [];

		// Get stored value
		var fieldName = field.data('name');
		var input = field.find(':input[name="' + fieldName + '[]"]');

		// Assign the stored value
		if (input.length > 0) {
			$.each(input, function(key, item) {
				var value = $(item).val();
				accumulativeValue.push(value);
			});
		}

		autocomplete.on('addItem', function(event, item) {
			accumulativeValue.push(item.value);
			self.fieldChanged(key, accumulativeValue);
		});

		autocomplete.on('removeItem', function(event, item) {
			accumulativeValue.splice($.inArray(item.value, accumulativeValue), 1);
			self.fieldChanged(key, accumulativeValue);
		});

		// Trigger initial page load
		if (!field.hasClass('t-hidden')) {
			self.fieldChanged(key, accumulativeValue);
		}

		// Re-check the current field value and determine if we should show or not the field
		field.on('onFieldShow', function() {
			self.fieldChanged(key, accumulativeValue);
		});
	},

	getRequiredChecking: function() {
		var data = $('[data-conditional-check]').val();

		if (data) {
			return JSON.parse(data);
		}

		return false;
	},

	fieldChanged: function(key, value) {

		// Get the latest value of the field
		if (!value) {
			var value = self.getTargetedFieldValue(key);
		}

		// Check conditions
		opts.result[key] = self.checkCondition(value, key);

		// Get the conditions logic
		var criteria = base.data('conditionsLogic');

		// Hide or show the field depending on the result
		if (opts.result) {
			var trueResult = true;

			$.each(opts.result, function(idx, result) {

				// OR criteria
				if (criteria == 'or') {

					// If there is one true, means everything is true
					if (result == true) {
						trueResult = true;
						return false;
					}
				}

				// AND criteria
				// All must be true in order to pass
				if (result == false) {
					trueResult = false;
				}
			});

			if (trueResult) {
				self.showSelf();
			} else {
				self.hideSelf();
			}
		}
	},

	checkCondition: function(value, key) {
		var condition = opts.conditionValue[key];
		var result = false;

		// format the value for special condition first
		value = self.formatValue(value, key);

		switch (opts.operator[key]) {
			case 'equal':

				if (self.isAutocomplete(key)) {
					$.each(value, function(idx, item) {
						if (item == condition) {
							result = true;
							return true;
						}
					});
				} else if (value == condition) {
					result = true;
				}
			break;

			case 'not equal':

				if (self.isAutocomplete(key)) {
					if (value.length > 0) {
						result = true;
						$.each(value, function(idx, item) {
							if (item == condition) {
								result = false;
								return true;
							}
						});
					}
				} else if (value != condition) {
					result = true;
				}
			break;

			case 'contain':
				if (self.isAutocomplete(key)) {
					$.each(value, function(idx, item) {
						result = new RegExp('\\b' + condition + '\\b', 'i').test(item);
					});
				} else {
					result = new RegExp( '\\b' + condition + '\\b', 'i').test(value);
				}
			break;
		}

		return result;
	},

	getTargetedFieldValue: function(key) {
		var field = self.getTargetedField(key);
		var fieldElement = self.getTargetedFieldElement(key);
		var inputs = field.find(':input');

		var value = [];
		var result = false;

		if (inputs.length > 0) {
			$.each(inputs, function() {
				var input = $(this);
				var type = input.attr('type');

				if (type != 'hidden') {
					switch (type) {
						case 'radio':
							radio = input.is(':checked') ? 1 : 0;

							// Only checked if the radio is selected, means that is the value
							if (radio) {
								value = input.val();
							}

						break;
						case 'checkbox':
							checkbox = input.is(':checked');

							if (checkbox) {
								value.push(input.val());
							}

						break;
						case 'button':
							// since button is not really an input, we need to check the hidden field instead
							var parent = input.parent();
							var inputButton = parent.find('input');

							if (inputButton.attr('type') == 'hidden') {
								value = inputButton.val();
							}
						break;
						default:
							if (input.val()) {
								value = input.val();
							}
						break;
					}
				}
			})
		}

		if ($.isArray(value)) {
			$.each(value, function(key, data) {
				result = result ? result + ' ' + data : data;
			})
		} else {
			result = value;
		}

		return result;
	},

	showSelf: function() {
		base.removeClass('t-hidden');
		base.trigger('onFieldShow');

		// Activate required field
		self.updateRequiredChecking(true);
	},

	hideSelf: function() {
		base.addClass('t-hidden');
		base.trigger('onFieldHide');

		// Disabled required field since the field is not visible on the form
		self.updateRequiredChecking(false);
	},

	updateRequiredChecking: function(operation) {
		// Get current required value
		var data = self.getRequiredChecking();

		if (data) {
			data[opts.id] = operation;
			data = JSON.stringify(data);

			$('[data-conditional-check]').val(data);
		}
	},

	isAutocomplete: function(key) {
		if (self.getTargetedFieldElement(key) == 'autocomplete') {
			return true;
		}

		return false;
	},

	getTargetedField: function(key) {
		var field = $('[data-field-item][data-id="' + opts.fieldTargeted[key] + '"]');

		return field;
	},

	getTargetedFieldElement: function(key) {
		var field = self.getTargetedField(key);
		return field.data('fieldItem');
	},

	formatValue: function(value, key) {

		var fieldElement = self.getTargetedFieldElement(key);

		var defaultGender = ["1", "2", "3"];

		// We check for gender male = 1 and female = 2 value
		if (fieldElement == 'gender' && value && $.inArray(value, defaultGender) >= 0) {
			value = value == "1" ? "male" : value == "3" ? "other" : "female";
		}

		return value;
	},

	formatConditionsValue: function(key) {
		var fieldElement = self.getTargetedFieldElement(key);

		// Properly check for gender value
		if (fieldElement == 'gender') {
			if (opts.conditionValue[key] == "1") {
				opts.conditionValue[key] = 'male';
			}

			if (opts.conditionValue[key] == "2") {
				opts.conditionValue[key] = 'female';
			}

			if (opts.conditionValue[key] == "3") {
				opts.conditionValue[key] = 'other';
			}
		}
	}
}});

module.resolve();
});
