EasySocial.module('site/audios/item', function($) {

var module = this;

EasySocial.Controller('Audios.Item', {
	defaultOptions: {

		tagAdding : null,
		"{wrapper}": "[data-audio-item]",

		"{tagPeople}": "[data-audio-tag]",
		"{tagsWrapper}": "[data-audio-tag-wrapper]",
		"{deleteButton}": "[data-audio-delete]",

		"{removeTag}": "[data-remove-tag]",
		"{tagItem}": "[data-tags-item]",

		"{featureButton}": "[data-audio-feature]",
		"{unfeatureButton}": "[data-audio-unfeature]",
		"{playlistItem}": "[data-playlist-item]"
	}
}, function(self, opts, base) { return {

	init: function() {
		opts.id = self.element.data('id');
	},

	"{unfeatureButton} click": function(unfeatureButton, event) {
		EasySocial.dialog({
			content: EasySocial.ajax("site/views/audios/confirmUnfeature", {
				"id": opts.id,
				"return": opts.callbackUrl
			})
		})
	},

	"{featureButton} click": function(featureButton, event) {

		EasySocial.dialog({
			content: EasySocial.ajax("site/views/audios/confirmFeature", {
				"id": opts.id,
				"return": opts.callbackUrl
			})
		});
	},

	"{deleteButton} click": function(deleteButton, event) {
		EasySocial.dialog({
			content: EasySocial.ajax("site/views/audios/confirmDelete", {
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

		EasySocial.ajax('site/controllers/audios/removeTag', {
			"id": id
		}).done(function() {
			opts.tagsExclusion.splice($.inArray(userId, opts.tagsExclusion), 1);
		});
	},

	"{tagPeople} click": function(tagPeople, event) {

		self.tagAdding = false;

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/audios/tagPeople', {
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

					// Make an ajax call to the server to tag people in this audio
					EasySocial.ajax('site/controllers/audios/tag', {
						"ids": ids,
						"id": opts.id
					}).done(function(tags) {

						if (! opts.tagsExclusion) {
							opts.tagsExclusion = [];
						}

						$.each(ids, function(i, id) {
							opts.tagsExclusion.push(id);
						});

						// Remove hidden class
						self.tagsWrapper().removeClass('t-hidden');

						// Just try to remove the is-empty on the wrapper.
						self.tagsWrapper().parent().removeClass('is-empty');

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
	},
	"{playlistItem} click": function(playlistItem, event) {
		var item = playlistItem.parents(self.wrapper.selector);
		var audioId = item.data('id');
		var playlistId = playlistItem.data('id');
		var previouslyAdded = playlistItem.find('i').length > 0;

		var player = item.find('[data-audio-player][data-id="' + audioId + '"]')
		var overlayNotice = player.find('[data-overlay-notice]');

		EasySocial.ajax('site/controllers/audios/addToPlaylist',{
			"playlistId": playlistId,
			"audioId": audioId
		}).done(function(message) {
			if (previouslyAdded === false) {
				playlistItem.append('<i class="fa fa-check pull-right"></i>');
			}

			overlayNotice.fadeIn('fast');

			setTimeout(function(){
				overlayNotice.fadeOut('slow');
			}, 2000);
		});
	}
}});

module.resolve();


});
