EasySocial.module('admin/workflows/choices', function($) {

var module = this;

EasySocial.Controller('Config.Choices', {
	defaultOptions: {
		'{choiceItems}' : '[data-fields-config-param-choice]',
		unique : 1
	}
}, function(self, opts, base) { return {
	init: function() {
		self.options.unique = self.element.data('unique') !== undefined ? self.element.data('unique') : 1;

		self.choiceItems().implement(EasySocial.Controller.Config.Choices.Choice, {
			controller: {
				'choices': self
			}
		});

		self.initSortable();
	},

	initSortable: function() {
		self.element.sortable({
			items: self.choiceItems.selector,
			placeholder: 'ui-state-highlight',
			cursor: 'move',
			forceHelperSize: true,
			handle: '[data-fields-config-param-choice-drag]',
			stop: function() {
				// Manually remove all the freezing tooltip due to conflict between bootstrap tooltip and jquery sortable
				$('.tooltip-es').remove();

				// Trigger change on parent field
				$('[data-fields-config-param]').trigger('change');
			}
		});
	}
}});

/* Config Choices Choice Controller */
EasySocial.Controller('Config.Choices.Choice', {
	defaultOptions: {
		'{choiceValue}': '[data-fields-config-param-choice-value]',
		'{choiceTitle}': '[data-fields-config-param-choice-title]',
		'{choiceDefault}': '[data-fields-config-param-choice-default]',
		'{addChoice}': '[data-fields-config-param-choice-add]',
		'{removeChoice}': '[data-fields-config-param-choice-remove]',
		'{setDefault}': '[data-fields-config-param-choice-setdefault]',

		'{defaultIcon}': '[data-fields-config-param-choice-defaulticon]'
	}
}, function(self, opts, base) { return {

	init: function() {
	},

	'{addChoice} click' : function() {
		// Clone a new item from current clicked element
		var newItem = self.element.clone();

		// Let's leave the value blank by default.
		var inputElement = newItem.find('input[type="text"]');

		inputElement.attr('value', '');

		inputElement.val('');

		// Set the default as 0 and the icon to unfeatured
		var inputDefault = newItem.find('input[type="hidden"]');

		inputDefault.attr('value', 0);

		inputDefault.val(0);

		// set id = 0
		newItem.attr('data-id', 0);
		newItem.data('id', 0);

		// Implement the controller for this choice
		newItem.implement(EasySocial.Controller.Config.Choices.Choice, {
			controller: {
				'choices': self.choices
			}
		});

		// Append this item
		self.element.after(newItem);
	},

	'{removeChoice} click' : function() {
		// We need to minus one because we're trying to remove ourself also.
		var remaining = self.choices.choiceItems().length - 1;

		// If this is the last item, we wouldn't want to allow the last item to be removed.
		if (remaining >= 1) {
			self.element.remove();

			// Manually remove the tooltip generated on the remove button
			$('.tooltip-es').remove();

			// Trigger event
			$('[data-fields-conditional-param]').trigger('change');
			$('[data-fields-config-param]').trigger('change');
		}
	},

	'{setDefault} click': function() {
		var index = self.element.index(),
			title = self.choiceTitle().val(),
			value = self.choiceValue().val();

		self.choices.choiceItems().trigger( 'toggleDefault', [index] );
	},

	'{self} toggleDefault': function(el, ev, i) {
		var index = self.element.index(),
			value = parseInt(self.choiceDefault().val());

		if (index === i) {
			if(value) {
				self.defaultIcon()
					.removeClass('es-state-featured')
					.addClass('es-state-default');

				self.choiceDefault().val(0);
			} else {
				self.defaultIcon()
					.removeClass('es-state-default')
					.addClass('es-state-featured');

				self.choiceDefault().val(1);
			}
		} else {
			if(self.choices.options.unique) {
				self.defaultIcon()
					.removeClass('es-state-featured')
					.addClass('es-state-default');

				self.choiceDefault().val(0);
			}
		}

		$('[data-fields-config-param]').trigger('change');
	}
}});

module.resolve();
});