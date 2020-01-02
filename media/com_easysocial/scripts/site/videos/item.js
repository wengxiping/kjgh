EasySocial.module('site/videos/item', function($) {

var module = this;

EasySocial.Controller('Videos.Item', {
	defaultOptions: {

		tagAdding	: null,

		"{tagPeople}": "[data-video-tag]",
		"{tagsWrapper}": "[data-video-tag-wrapper]",
		"{deleteButton}": "[data-video-delete]",

		"{removeTag}": "[data-remove-tag]",
		"{tagItem}": "[data-tags-item]",

		"{featureButton}": "[data-video-feature]",
		"{unfeatureButton}": "[data-video-unfeature]",

		"{embed}": "[data-video-embed]"
	}
}, function(self, opts, base) { return {

	init: function() {
		opts.id = self.element.data('id');
	},

	"{embed} click": function(button, event) {
		EasySocial.dialog({
			content: EasySocial.ajax("site/views/videos/embed", {
				"id": opts.id
			})
		});
	},

	"{unfeatureButton} click": function(unfeatureButton, event) {
		EasySocial.dialog({
			content: EasySocial.ajax("site/views/videos/confirmUnfeature", {
				"id": opts.id,
				"return": opts.callbackUrl
			})
		});
	},

	"{featureButton} click": function(featureButton, event) {

		EasySocial.dialog({
			content: EasySocial.ajax("site/views/videos/confirmFeature", {
				"id": opts.id,
				"return": opts.callbackUrl
			})
		});
	},

	"{deleteButton} click": function(deleteButton, event) {
		EasySocial.dialog({
			content: EasySocial.ajax("site/views/videos/confirmDelete", {
				"id": opts.id
			}),
			bindings: {

			}
		})
	},

	"{removeTag} click": function(removeTag, event) {

		var parent = removeTag.parents(self.tagItem.selector);
		var id = parent.data('id');

		var userId = parent.find('[data-user-id]').data('user-id');
		var userId = userId.toString();

		parent.remove();

		// If the length is only 1, we know that it's empty
		if (self.tagsWrapper().children().length == 0) {
			self.tagsWrapper().parent().addClass('is-empty');
		}

		EasySocial.ajax('site/controllers/videos/removeTag', {
			"id": id
		}).done(function() {
			opts.tagsExclusion.splice($.inArray(userId, opts.tagsExclusion), 1);
		});
	},

	"{tagPeople} click": function(tagPeople, event) {

		self.tagAdding = false;

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/videos/tagPeople', {
							"id": opts.id,
							"exclusion": opts.tagsExclusion,
							"clusterId": opts.clusterId,
							"clusterType": opts.clusterType
						}),
			bindings: {
				"{submit} click": function(submitButton, event) {

					if (self.tagAdding) {
						return;
					}

					// now we set the state
					self.tagAdding = true;

					var suggest = this.suggest().textboxlist("controller");
					var items = suggest.getAddedItems();

					if (items.length <= 0) {
						return;
					}

					var ids = $.pluck(items, "id");

					// Make an ajax call to the server to tag people in this video
					EasySocial.ajax('site/controllers/videos/tag', {
						"ids": ids,
						"id": opts.id
					}).done(function(tags) {

						if (! opts.tagsExclusion) {
							opts.tagsExclusion = [];
						}

						$.each(ids, function(i, id) {
							opts.tagsExclusion.push(id);
						});

						// Just try to remove the is-empty on the wrapper.
						self.tagsWrapper().parent().removeClass('is-empty');

						self.tagsWrapper().removeClass('t-hidden');


						// Append the tags to the wrapper
						self.tagsWrapper().append(tags);

						//clear items in dialog to avoid user click insert multiple time
						suggest.clearItems();

						// Hide the dialog
						EasySocial.dialog().close();

						// unset the state
						self.tagAdding = false;
					});
				}
			}
		})
	}
}});

module.resolve();


});
