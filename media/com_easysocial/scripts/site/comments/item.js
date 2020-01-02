EasySocial.module('site/comments/item', function($) {
var module = this;

EasySocial.require()
.library('mentions')
.done(function() {

EasySocial.Controller('Comments.Item', {
	defaultOptions: {
		'id': 0,
		'child': 0,
		'loadedChild': 0,
		'limit': 10,
		'isNew': false,

		// Comment item wrapper
		'{wrapper}': '[data-comment-wrapper]',

		// Content of the comment
		'{content}': '[data-comment-content]',

		// Editing
		'{editor}': '[data-comment-editor]',
		'{edit}': '[data-comment-actions] [data-edit]',
		'{input}'	: '[data-comment-input]',
		'{cancel}': "[data-comment-editor] [data-cancel]",
		'{save}': "[data-comment-editor] [data-save]",

		// Actions
		'{delete}': '[data-comment-actions] [data-delete]',
		'{loadReplies}': '[data-comments-item-loadReplies]',
		'{readMore}': '[data-es-comment-readmore]',
		'{fullContent}': '[data-es-comment-full]',

		emoticons: []
	}
}, function(self, opts) { return {
	init: function() {

		// Get available hints for friend suggestions and hashtags
		opts.hints = {
				"friends": $('[data-hints-friends]'),
				"hashtags": $('[data-hints-hashtags]'),
				"emoticons": $('[data-hints-emoticons]')
		};

		// Initialise comment id
		opts.id = self.element.data('id');

		// Initialise child count
		opts.child = self.element.data('child');

		// Register self into the registry of comments
		self.parent.registerComment(self);
	},

	'{edit} click': function(link, event) {

		if (!link.enabled()) {
			return;
		}

		// Get the editor
		var editor = self.editor();
		var mentions = editor.controller('mentions');

		// Manually clear out the html and destroy the mentions controller to prevent conflict of loading the editFrame again.
		editor.empty();

		if (mentions) {
			mentions.destroy();
		}

		// Disable the edit link
		link.disabled(true);

		// Trigger commentEditLoading event
		// self.trigger('commentEditLoading', [self.options.id]);

		EasySocial.ajax('site/controllers/comments/edit', {
			id: self.options.id
		}).done(function(contents) {

			// Hide the current contents and show the editor
			self.wrapper().hide();
			self.editor().show();

			// Append the form to the edit frame
			self.editor().html(contents).show();
			self.setMentionsLayout();

			self.input().focus();
		});
	},

	setMentionsLayout: function() {
		var editor = self.editor();
		var mentions = editor.controller("mentions");

		if (mentions) {
			mentions.cloneLayout();
			return;
		}

		editor.mentions({
			triggers: {
				"@": {
					type: "entity",
					wrap: false,
					stop: "",
					allowSpace: true,
					finalize: true,
					query: {
						loadingHint	: true,
						searchHint: opts.hints.friends.find('[data-search]').html(),
						emptyHint: opts.hints.friends.find('[data-empty]').html(),
						data: function (keyword) {

							var task = $.Deferred();

							EasySocial.ajax( "site/controllers/friends/suggest" , {
								"search": keyword,
								clusterId: self.parent.options.clusterid
							}).done(function(items) {

								if (!$.isArray(items)) {
									task.reject();
								}

								var items = $.map(items, function(item){

									var html = $('<div/>').html(item);
									var title = html.find('[data-suggest-title]').val();
									var id = html.find('[data-suggest-id]').val();

									return {
										"id": id,
										"title": title,
										"type": "user",
										"menuHtml": item
									};
								});

								task.resolve(items);
							})
							.fail(task.reject);

							return task;
						},
						use: function(item) {
							return item.type + ":" + item.id;
						}
					}
				},
				"#": {
					type: "hashtag",
					wrap: true,
					stop: " #",
					allowSpace: false,
					query: {
						loadingHint	: true,
						searchHint: opts.hints.hashtags.find('[data-search]').html(),
						emptyHint: opts.hints.hashtags.find('[data-empty]').html(),

						data: function(keyword) {

							var task = $.Deferred();

							EasySocial.ajax("site/controllers/hashtags/suggest", {
								search: keyword
							}).done(function(items) {
								if (!$.isArray(items)) {
									task.reject();
								}

								var items = $.map(items, function(item) {

									return {
										"title": "#" + $.trim(item),
										"type": "hashtag",
										"menuHtml": item
									};
								});

								task.resolve(items);
							}).fail(task.reject);

							return task;
						}
					}
				},
				":": {
						type: "emoticon",
						wrap: true,
						stop: "",
						allowSpace: false,
						query: {
							loadingHint: true,
							searchHint: opts.hints.emoticons.find('[data-search]').html(),
							emptyHint: opts.hints.emoticons.find('[data-empty]').html(),
							data: $.parseJSON(self.parent.options.emoticons),
							renderAll: true
						}
					}
			},
			plugin: {
				autocomplete: {
					id: "es",
					component: "es",
					position: {
						my: 'left top',
						at: 'left bottom',
						of: editor,
						collision: 'none'
					},
					size: {
						width: function() {
							return editor.width();
						}
					}
				}
			}
		});
	},

	"{input} keyup": function(element, event) {
		// Prevent cursor keys from moving between photos
		event.preventDefault();
		event.stopPropagation();

	},

	'{cancel} click': function() {
		// Destroy the editor
		self.editor().empty();
		self.wrapper().show();

		self.edit().enabled(true);
	},

	'{save} click': function() {

		// Get and trim the edit value
		var input = self.input().val();

		// Do not proceed if value is empty
		if (input == '') {
			return false;
		}

		// Trigger commentEditSaving event
		// self.trigger('commentEditSaving', [self.options.id, input]);

		var mentions = self.editor().mentions("controller");

		EasySocial.ajax('site/controllers/comments/update', {
			"id": self.options.id,
			"input": self.input().val(),
			"mentions": mentions ? mentions.toArray() : []
		}).done(function(comment) {

			// Update the comment content
			self.content().html(comment);

			// Hide the editor
			self.editor().empty().hide();
			self.wrapper().show();

			self.edit().enabled(true);
		});
	},

	'{delete} click': function(el) {

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/comments/confirmDelete', {
				id: self.options.id
			}),
			bindings: {
				"{deleteButton} click": function() {

					// Close the dialog
					EasySocial.dialog().close();

					EasySocial.ajax('site/controllers/comments/delete', {
						id: self.options.id
					}).done(function() {

						// Trigger commentDeleted event on parent, since this element will be remove, no point triggering on self
						self.parent.trigger('commentDeleted', [self.options.id]);

					});
				}
			}
		});
	},

	'{loadReplies} click': function(el) {
		// Hide the loadReplies button
		el.hide();

		// Add a loader after this comment first

		// Calculate the start
		var start = Math.max(self.options.child - self.options.loadedChild - self.options.limit, 0);

		// Get the child comments
		EasySocial.ajax()
			.done(function(comments) {

				// Append the comments below the current comment item
				$.each(comments, function(index, comment) {
					self.parent.$List.addToList(comment, 'child', false);
				});

				// Trigger oldCommentsLoaded event
				self.parent.trigger('oldCommentsLoaded', [comments]);

				// Check if we need to show the load more replies button in the current item
				start > 0 && self.loadMoreReplies().show();
			});
	},

	'{readMore} click': function(el, ev) {
		self.content().html(self.fullContent().html());
	}
}});

module.resolve();

});

});
