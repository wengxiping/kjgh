
EasySocial.require()
.done(function($) {

EasySocial.Controller('Apps.Tasks', {
	defaultOptions: {

		// New task button
		"{create}": "[data-create]",
		"{form}": "[data-form]",

		// New task form
		"{title}": "[data-form-title]",
		"{cancel}": "[data-form-cancel]",
		"{save}": "[data-form-save]",

		// Contents
		"{content}": "[data-app-contents]",
		"{lists}": "[data-lists]",

		// Item
		"{item}": "[data-item]",
		"{checkbox}": "[data-item-checkbox]",
		"{delete}": "[data-item-delete]",

		// Filters
		"{filter}": "[data-tasks-filter]",
		"{filterLinks}" : "[data-tasks-filter] > a"
	}
}, function(self, opts) { return {

	removeItem: function(item) {

		item.remove();

		// Determines if there's any else left on the page
		if (self.item().length == 0) {
			self.content().addClass('is-empty');
		}
	},

	insertItem: function(item) {
		item.prependTo(self.lists());
	},

	"{create} click" : function() {

		// If this is on mobile, we need to toggle the filter
		if (window.es.mobile) {
			var filter = $('[data-es-mobile-filters]');

			if (filter.length > 0) {
				filter.click();
			}
		}

		self.content().removeClass('is-empty');
		self.form().removeClass('t-hidden');
	},


	"{save} click" : function() {
		var val = self.title().val();

		if (val == "") {
			return;
		}

		EasySocial.ajax('apps/user/tasks/controllers/tasks/save', {
			"title"	: self.title().val()
		}).done(function(item) {
			self.insertItem($(item));
			self.title().val('');
		});
	},

	"{title} keyup" : function(input, event) {

		// Enter key
		if (event.keyCode == 13) {
			self.save().click();
		}

		// Escape key
		if (event.keyCode == 27) {
			self.cancel().click();
		}
	},

	"{cancel} click" : function() {
		self.title().val('');
		self.form().addClass('t-hidden');
	},

	"{filter} click" : function(filter, event) {
		var type = filter.data('tasks-filter');

		// Set active filter
		self.filter().removeClass('active');
		filter.addClass('active');

		// Show all
		if (type == 'all') {
			var total = self.item().show();

			if (!total.length) {
				self.content().addClass('is-empty');
				return;
			}

			self.content().removeClass('is-empty');

			return;
		}
		
		// Remove empty state
		self.content().removeClass('is-empty');		

		// Hide all items
		self.item().hide();

		// Show only specific types.
		var total = self.item('.' + type).show();

		// If there is no content, add the empty state
		if (!total.length) {
			self.content().addClass('is-empty');
		}
	},

	"{checkbox} change" : function(checkbox, event) {
		var checked = checkbox.is(":checked");
		var task = checked ? 'resolve' : 'unresolve';
		var item = checkbox.closest(self.item.selector);
		var id = item.data('id');

		if (task == 'resolve') {
			item.removeClass('is-unresolved').addClass('is-resolved');
		}

		if (task == 'unresolve') {
			item.removeClass('is-resolved').addClass('is-unresolved');
		}

		EasySocial.ajax('apps/user/tasks/controllers/tasks/' + task, {
			"id": id
		}).done(function() {
		});
	},

	"{delete} click" : function(link, event) {
		var item = link.closest(self.item.selector);
		var id = item.data('id');

		EasySocial.ajax( 'apps/user/tasks/controllers/tasks/remove' , {
			"id": id
		}).done(function() {
			self.removeItem(item);
		});
	}

}});


// Implement the controller.
$('[data-es-tasks]').implement(EasySocial.Controller.Apps.Tasks);

});
