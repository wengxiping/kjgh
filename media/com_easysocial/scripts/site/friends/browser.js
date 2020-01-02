EasySocial.module('site/friends/browser', function($) {

var module 	= this;

EasySocial.require()
.script('site/friends/suggest')
.done(function($) {

EasySocial.Controller('Friends.Browser', {
	defaultOptions: {
		// Get the default active list if there is any.
		activeList: null,

		// Contents
		"{wrapper}": "[data-wrapper]",
		"{contents}": "[data-contents]",
		"{items}": "[data-items]",
		"{item}": "[data-item]",
		"{pagination}": "[data-pagination]",

		// Friend list actions
		"{listActions}": "[data-list-actions]",
		"{deleteList}": "[data-list-actions] [data-delete]",
		"{defaultList}": "[data-list-actions] [data-default]",
		"{addToList}": "[data-list-actions] [data-add]",
		"{removeFromList}": "[data-remove-from-list]"
	}
}, function(self, opts) { return {

	init: function() {
		opts.userId = self.element.data('userid');
	},

	insertItem: function(item) {

		// Hide any empty notices.
		self.items().removeClass('is-empty');

		// Update the counter for the list items.
		self.triggerUpdateListCounters();

		// Prepend the result back to the list
		$(item).prependTo(self.items());
	},

	triggerUpdateListCounters: function() {
		self.trigger('onEasySocialUpdateFriendListCounters');
	},

	triggerUpdateCounters: function() {
		self.trigger('onEasySocialUpdateFriendCounters');
	},

	// Update the content on the friends list.
	updateContents: function(html) {
		self.contents().html(html);

		$('body').trigger('afterUpdatingContents', [html]);
	},

	getFriends: function(filter, listId, callback) {
		self.wrapper().addClass('is-loading');
		self.contents().empty();

		var options = {
			"filter": filter,
			"userid": opts.userId
		};

		EasySocial.ajax("site/controllers/friends/filter", {
			"filter": filter,
			"userid": opts.userId,
			"id": listId !== undefined ? listId : ''
		}).done(function(html) {

			self.updateContents(html);

			// trigger sidebar toggle for responsive view.
			self.trigger('onEasySocialFilterClick');

		}).always(function() {
			self.wrapper().removeClass('is-loading');

			if (typeof(callback) == 'function') {
				callback.apply();
			}
		});
	},

	removeItem: function(id, source) {
		// Remove item from the list.
		var item = self.item('[data-id="' + id + '"]');

		item.remove();

		if (self.item().length <= 0) {
			self.items().addClass('is-empty');
			self.pagination().remove();
		}

		// Update the counter for the list items.
		if (source == 'list') {
			self.triggerUpdateListCounters();
			return;
		}

		self.triggerUpdateCounters();
	},

	"{addToList} click": function(link) {

		var wrapper = link.parents('[data-list-actions]');
		var id = wrapper.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/friends/assignList', {"id" : id}),
			bindings: {

				"{insertButton} click" : function() {
					var items = this.suggest().textboxlist("controller").getAddedItems();

					EasySocial.ajax('site/controllers/friends/assign', {
						"uid": $.pluck(items, "id"),
						"listId": id
					}).done(function(contents) {

						// Hide any notice messages.
						$('[data-assignFriends-notice]').hide();

						$(contents).each(function(i, item) {

							// Pass the item to the parent so it gets inserted into the friends list.
							self.insertItem(item);

							// Close the dialog
							EasySocial.dialog().close();
						});

					}).fail(function(message) {
						$('[data-assignFriends-notice]').addClass('alert alert-error')
							.html(message.message);
					});
				}
			}
		});
	},

	"{deleteList} click" : function(link) {
		var actions = link.parents(self.listActions.selector);
		var id = actions.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax("site/views/friends/confirmDeleteList", {"id": id}),
			bindings: {
				"{deleteButton} click" : function() {
					$('[data-friends-list-delete-form]').submit();
				}
			}
		});
	},

	"{removeFromList} click" : function(link) {
		var id = link.parents(self.item.selector).data('id');

		EasySocial.ajax('site/controllers/friends/removeFromList', {
			"listId": self.options.activeList,
			"userId": id
		}).done(function() {

			self.removeItem(id, 'list');
		});
	},
}});

module.resolve();
});
});
