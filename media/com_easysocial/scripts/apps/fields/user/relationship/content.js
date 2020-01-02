EasySocial.module('apps/fields/user/relationship/content', function($) {

var module = this;

EasySocial
.require()
.script('site/friends/suggest')
.done(function($) {

EasySocial.Controller('Field.Relationship', {
	defaultOptions: {
		required: false,
		id: null,
		types: null,
		fieldname: null,
		actor: null,
		target: null,
		userid: null,

		'{type}': '[data-rs-type]',
		'{request}': '[data-rs-request]',

		'{field}': '[data-field-relationship]',
		'{pendingTitle}': '[data-rs-pending-title]',

		// Relations wrapper
		'{relations}': '[data-rs-relations]',

		// Delete a relation
		'{delete}': '[data-rs-delete]',

		// Request form
		'{rejectRequest}': '[data-rs-reject]',
		'{approveRequest}': '[data-rs-approve]'
	}
}, function(self, opts, base) { return {
	init: function() {

		EasySocial.module('field.relationship/' + opts.id).done(function(types) {
			self.options.types = types;
		});

		self.addPlugin('form');
	},

	getRequestId: function() {
		return self.request().data('id');
	},

	getRequestWrapper: function(id) {
		wrapper = $('[data-rs-request][data-id="' + id + '"]');
		return wrapper;
	},

	showTypeSelection: function() {
		// Show the form
		self.type().val(self.type().children(':first').val());
		self.type().removeClass('t-hidden').show();
		self.type().removeAttr('disabled');
	},

	'{self} relationshipDeleted': function() {
		self.showTypeSelection();
	},

	'{delete} click': function(button, event) {
		event.stopPropagation();
		event.preventDefault();

		button.addClass('is-loading');

		EasySocial.ajax('fields/user/relationship/delete', {
			"id": opts.id,
			"userid": opts.userid,
			"relid": self.getRequestId()
		}).done(function(output) {

			button.removeClass('is-loading');

			self.relations().remove();

			// Show the type selection again
			self.showTypeSelection();
		});
	},

	'{approveRequest} click': function(button, event) {
		var id = button.data('id');
		button.addClass('is-loading');

		EasySocial.ajax('fields/user/relationship/approve', {
			"id": opts.id,
			"relid": id,
			"inputName": opts.fieldname
		}).done(function(output) {

			// Remove hidden input
			$('[data-rs-request-hidden]').remove();

			wrapper = self.getRequestWrapper(id);

			button.removeClass('is-loading');
			self.request().not(wrapper).remove();


			wrapper.replaceWith(output);
		});
	},

	'{rejectRequest} click': function(button, event) {
		event.preventDefault();
		event.stopPropagation();

		var id = button.data('id');

		EasySocial.ajax('fields/user/relationship/reject', {
			"id": opts.id,
			"relid": id
		}).done(function() {
			// Hide the request item
			wrapper = self.getRequestWrapper(id);

			wrapper.remove();
			self.trigger('relationshipDeleted');
		});
	}
}});

EasySocial.Controller('Field.Relationship.Form', {
	defaultOptions: {

		'{field}': '[data-field-relationship]',
		'{type}': '[data-rs-type]',

		'{input}': '[data-rs-input]',
		'{target}': '[data-rs-target]',

		'{targetAvatar}': '[data-rs-target-avatar]',
		'{targetName}': '[data-rs-target-name]',
		'{targetDelete}': '[data-rs-form-delete]',
		'{textboxlistDelete}': '[data-textboxlist-itemRemoveButton]',

		target : false
	}
}, function(self, opts, base) { return {
	init: function() {
		self.input().addController(EasySocial.Controller.Friends.Suggest, {
			max: 1,
			name: self.parent.options.fieldname + '[target][]'
		});

        var data = self.field().htmlData();
        if (data && data.error) {
			opts.error = data.error;
        }

        // default this to true so that edit whitout changing the relationship will still pass. #492
		opts.target = true;

	},

    "{self} onRender": function() {
        var data = self.field().htmlData();
        opts.error = data.error;
    },

	'{type} change': function(dropdown, event) {

		var type = dropdown.val();
		var option = dropdown.find(':selected');
		var connection = option.data('connection') ? true : false;
		opts.target = false;

		// If this relationship does not have any connection, skip this
		if (!connection) {
			opts.target = true;

			self.clearError();
			self.input().hide();
			self.target().hide();
			return;
		}

		// Always remove the item when changing a type
		self.input().controller('Textboxlist').clearItems();
		self.target().hide();
		self.input().show();
		self.validateInput(opts.error.target);
	},

	'{input} addItem': function(el, ev, item) {

		// Since the item is html based, we need to extract the data
		var item = $('<div/>').html(item.html);
		var avatar = item.find('[data-suggest-avatar]').attr('src');
		var name = item.find('[data-suggest-title]').val();
		var id = item.find('[data-suggest-id]').val();

		if (avatar) {
			self.targetAvatar().attr('src', avatar);
		}

		if (name) {
			self.targetName().text(name);
		}

		if (id) {
			self.targetDelete().data('id', id);
		}

		self.input().hide();
		self.target().show();

		// Globally set the target is true
		opts.target = true;
		self.clearError();
	},

	'{input} blur' : function(el, ev) {
		self.validateInput(opts.error.target);
	},

	'{targetDelete} click': function(button, event) {
		var id = button.data('id');

		// Remove the selected item
		self.input().controller('Textboxlist').removeItem(id);

		self.type().removeAttr('disabled');
		self.input().show();
		self.target().hide();

		opts.target = false;
		self.validateInput(opts.error.target);
	},

	'{self} onSubmit': function(el, event, register) {

		register.push(self.validateInput(opts.error.target));
	},

	validateInput : function(msg) {

        var isRequired = self.parent.options.required;
        var type = self.type().val();

        var allowed = ['na', 'single', 'widowed', 'separated', 'divorced', 'relationshipnotarget', 'engagednotarget', 'marriednotarget', 'complicatednotarget'];

        if(isRequired && type == 'na') {
			self.raiseError(opts.error.required);
			return false;
        }

        if (!isRequired && ($.inArray(type, allowed) !== -1)) {
			self.clearError();
			return true;
        }

        if (($.inArray(type, allowed) === -1) && !opts.target) {
			self.raiseError(opts.error.target);
			return false;
        }

        // all pass.
		self.clearError();
		return true;
	},

	raiseError: function(msg) {
		if (!msg) {
			msg = opts.error.required;
		}

		self.trigger('error', [msg]);
	},

	clearError: function() {
		self.trigger('clear');
	}
}});

module.resolve();

});
});
