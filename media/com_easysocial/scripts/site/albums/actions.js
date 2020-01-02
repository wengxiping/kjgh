EasySocial.module("site/albums/actions", function($){
var module = this;

// Load non essential dependencies
EasySocial.require()
.script("site/photos/item")
.done();

EasySocial.Controller("Albums.Actions", {
	hostname: "actions",

	defaultOptions: {
		"{photoItem}": "[data-photo-item]",
		"{imageLink}" : "[data-photo-image-link]",

		// Affix Header
		'{affix}': '[data-bs-spy=affix]',

		// Checkbox
		'{checkbox}': '[data-photo-item-checkbox]',
		'{checkAll}': '[data-photo-item-checkall]',

		// Actions button
		'{actionsWrapper}': '[data-photo-actions-wrapper]',
		'{actionsApply}': '[data-photo-actions-apply]',
		"{actionsTask}": '[data-photo-actions-task]'
	}
}, function(self, opts, base) { return {

	init: function() {
		self.albumId = base.data("album-id");

		// Calculate affix width
		self.updateHeader();

		$(window).resize(function() {
			self.updateHeader();
		});
	},

	updateHeader: function() {
		var width = self.element.width();
		var offset = self.affix().offset();

		self.affix()
			.css('width', width);

		// dynamically reset the top offset in affix
		self.affix().affix({
			offset: {
				top: offset.top - 20
			}
		});
	},

	getSelectedCheckbox: function() {
		var items = [];
		var selected = self.checkbox(':checked');

		selected.each(function(i, el) {
			items.push($(el).val());
		});

		return items;
	},

	"{self} afterLoadMore": function() {
		self.updateCheckboxSelection();
	},

	updateCheckboxSelection: function() {
		// Check if all checkbox is selected
		if (self.isAllCheckboxSelected()) {
			self.checkAll().prop('checked', true);
		} else {
			self.checkAll().prop('checked', false);
		}
	},

	isAllCheckboxSelected: function() {
		var totalSelected = self.getSelectedCheckbox().length;
		var totalCheckbox = self.checkbox().length;

		if (totalSelected == totalCheckbox) {
			return true;
		}

		return false;
	},

	clearSelectedCheckboxes: function() {
		self.checkAll().prop('checked', false);
		self.checkAll().trigger('change');
	},

	'{checkAll} change': function(input, event) {
		var checked = input.is(':checked');

		if (checked) {
			self.actionsWrapper().removeClass('t-hidden');
		} else {
			self.actionsWrapper().addClass('t-hidden');
		}

		self.checkbox().not(':disabled').prop('checked', checked);
		self.checkbox().not(':disabled').trigger('change');
	},

	"{checkbox} change" : function(input, event) {
		var selected = self.getSelectedCheckbox();
		var parent = input.parents(self.photoItem.selector);

		parent.removeClass('is-selected');

		// Check if all checkbox is selected
		self.updateCheckboxSelection();

		if (selected.length > 0) {
			self.actionsWrapper().removeClass('t-hidden');
			parent.addClass('is-selected');
			return;
		}

		self.actionsWrapper().addClass('t-hidden');
	},

	"{imageLink} click": function(image, event) {
		event.preventDefault();

		var parent = image.parents(self.photoItem.selector),
			checkbox = parent.find(self.checkbox.selector);

		checked = checkbox.is(':checked');

		checkbox.prop('checked', !checked);
		checkbox.trigger('change');
	},

	"{actionsApply} click": function(button, event) {
		var controllerTask = $.trim(self.actionsTask().val());

		if (controllerTask == '') {
			return false;
		}

		var confirmation = self.actionsTask().find(':selected').data('confirmation');

		// If there is no confirmation, just submit the form
		if (!confirmation) {
			self.taskInput().val(controllerTask);
			self.submitForm();

			return false;
		}

		// Get selected items
		var items = self.getSelectedCheckbox();

		EasySocial.dialog({
			"content": EasySocial.ajax(confirmation, {'ids': items, 'albumId': self.albumId, 'uid': opts.uid, 'type': opts.type}),
			"bindings": {
				"{submitButton} click": function(submitButton) {
					var dialog = this.parent;
					var submitButton = $(submitButton);
					var submitValue = '';

					// Get submitted value
					if (this.submitValue != undefined) {
						var submitValue = this.submitValue().val();
					}

					submitButton.disabled(true);

					var task = EasySocial.ajax(controllerTask, {
								"ids": items,
								"value": submitValue
							}).always(function() {
								dialog.close();
							});

					var trigger = self.actionsTask().find(':selected').data('trigger');

					if (trigger) {
						var total = items.length;

						$.each(items, function(idx, photoId) {

							// Get photo object
							var selector = $('[data-photo-item][data-photo-id=' + photoId + ']');
							var photo = selector.addController(EasySocial.Controller.Photos.Item);

							photo.trigger(trigger, [task, photo, true, submitValue]);

							// Trigger album pagination when all photos is deleted
							if (total - 1 === idx) {
								if (trigger == 'photoDelete' || trigger == 'photoMove') {
									photo.trigger('loadmore', [task, photo, total]);
								}
							}
						});
					}

					self.clearSelectedCheckboxes();
				}
			}
		});
	}
}});

module.resolve();

});
