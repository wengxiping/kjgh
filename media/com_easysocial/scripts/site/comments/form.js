EasySocial.module('site/comments/form', function($) {

var module = this;

EasySocial.Controller('Comments.Form', {
	defaultOptions: {
		'{editor}': '[data-comments-editor]',
		'{input}': '[data-comments-form-input]',
		'{submit}': '[data-comments-form-submit]',

		// Smileys
		"{smileyLink}": "[data-comment-smileys]",
		"{smileyItem}": "[data-comment-smiley-item]",

		// Attachments
		"{attachmentQueue}": "[data-comment-attachment-queue]",
		"{attachmentProgress}": "[data-comment-attachment-progress-bar]",
		"{attachmentBackground}": "[data-comment-attachment-background]",
		"{attachmentRemove}": "[data-comment-attachment-remove]",
		"{attachmentItem}": "[data-comment-attachment-item]",

		"{attachmentDelete}": "[data-comment-attachment-delete]",

		"{uploaderForm}": "[data-uploader-form]",
		"{itemTemplate}": "[data-comment-attachment-template]",

		"{dismissError}": "[data-comment-error-dismiss]",
		"{errorMessage}": "[data-comment-error]",
		"{errorCommentMessage}": "[data-comment-error-message]",

		"{uploadItem}": "[data-comment-photo-upload-item]",

		attachmentIds:[],
		emoticons: []
	}
}, function(self, opts, base, parent) { return {

	init: function() {

		// Assign the parent
		parent = self.parent;

		// Get available hints for friend suggestions and hashtags
		opts.hints = {
				"friends": $('[data-hints-friends]'),
				"hashtags": $('[data-hints-hashtags]'),
				"emoticons": $('[data-hints-emoticons]')
		};

		// Apply the mentions on the comment form
		self.setMentionsLayout();

		// Implement attachments on the comment form.
		if (parent.options.attachments) {
			self.implementAttachments();
		}

	},

	attachmentTemplate: null,

	getAttachmentTemplate: function() {

		if (!self.attachmentTemplate) {
			self.attachmentTemplate = self.itemTemplate().detach();
		}

		var tpl = $(self.attachmentTemplate).clone().html();

		return $(tpl);
	},

	implementAttachments: function() {

		// Implement uploader controller
		self.editor().implement(EasySocial.Controller.Uploader, {
			'temporaryUpload': true,
			'query': 'type=comments',
			'type': 'comments',
			extensionsAllowed: 'jpg,jpeg,png,gif'
		});

	},

	// determine whether uploading image complete or not
	hasUploadItems: function() {

		var	hasUploadItem = self.uploadItem().length > 0;

		return hasUploadItem;
	},

	"{smileyItem} click": function(smileyItem, event) {

		var value = smileyItem.data('comment-smiley-value');
		var editor = self.editor();

		// Add additional space to allow multiple smiley to be click at once. #3122
		value = value + ' ';

		// Get the input
		var isEditing = smileyItem.parents('[data-comment-editor]').length > 0 ? true : false;

		if (isEditing) {
			editor = smileyItem.parents('[data-comment-editor]');
		}

		var controller = editor.mentions("controller");
		var textarea = controller.textarea();

		previousCursor = controller.previousCursorPosition;

		var currentValue = textarea.val();
		var beforeValue = currentValue.substring(0, previousCursor) + value;
		var newValue = beforeValue + currentValue.substring(previousCursor);

		// We need to trigger the mention
		controller.isPasting = true;
		controller.smileyLength = value.length;

		textarea.val(newValue);
		textarea.trigger('input');

		controller.moveCursor(beforeValue.length);
	},

	"{smileyLink} click": function(smileyLink, event) {

		if (smileyLink.hasClass('active')) {
			smileyLink.removeClass('active');
			return;
		}

		smileyLink.addClass('active');
	},

	"{attachmentDelete} click": function(deleteLink, event) {

		var attachmentId = deleteLink.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/comments/confirmDeleteCommentAttachment', {
							"id": attachmentId
						}),
			bindings: {
				"{deleteButton} click": function() {

					// Perform an ajax call to the server
					EasySocial.ajax('site/controllers/comments/deleteAttachment', {
						"id": attachmentId
					})
					.done(function() {
						// Remove the dom from the page
						var item = deleteLink.parents(self.attachmentItem.selector);
						item.remove();

						EasySocial.dialog().close();
					});
				}
			}
		});

	},

	"{attachmentRemove} click": function(removeLink, event) {
		var item = removeLink.parents(self.attachmentItem.selector);

		// Remove the item from the attachment ids
		opts.attachmentIds = $.without(opts.attachmentIds, item.data('id'));

		// Remove the item
		item.remove();

		if (self.attachmentItem().length < 1) {
			self.attachmentQueue().removeClass('has-attachments');
		}
	},

	// When a new item is added, we want to display
	"{uploaderForm} FilesAdded": function(el, event, uploader, files) {

		$.each(files, function(index, file) {
			// Get the attachment template
			var item = self.getAttachmentTemplate();

			// Set the queue to use has-attachments class
			self.attachmentQueue()
				.addClass('has-attachments');

			// Insert the item into the queue
			item.attr('id', file.id)
				.addClass('is-uploading')
				.attr('data-comment-photo-upload-item', '1')
				.prependTo(self.attachmentQueue());
		});
	},

	// When the file is uploaded, we need to remove the uploading state
	"{uploaderForm} FileUploaded": function(el, event, uploader, file, response) {

		var item = self.attachmentQueue().find('#' + file.id);

		// Add preview
		self.attachmentBackground.inside(item)
			.css('background-image', 'url("' + response.preview + '")');

		// Remove the is-uploading state on the upload item
		item.removeClass('is-uploading');

		// Push the id
		item.data('id', response.id);

		opts.attachmentIds.push(response.id);

		// prevent user click submit button quickly caused show broken image in comment
		setTimeout(function() {

			// Determine whether this is the last image uploading
			// If yes, then activate the submit button here
			if (self.uploadItem().length < 2) {
				self.submit().removeAttr('disabled');
			}

			// Remove this data attribute once finish uploaded
			item.removeAttr('data-comment-photo-upload-item');

		}, 1);

	},

	// When item is being uploaded
	"{uploaderForm} UploadProgress" : function(el, event, uploader, file) {

		// Deactivate comment submit button to prevent users submitting the comment
		self.submit().attr('disabled', 'disabled');

		var item = $('#' + file.id);
		var progress = self.attachmentProgress.inside(item);

		progress.css('width', file.percent + '%');
	},

	'{input} keydown': function(el, event) {
		// Only allow control + shift or cmd + enter to submit comments
		if ((event.metaKey || event.ctrlKey) && event.keyCode == 13 && !self.hasUploadItems()) {
			self.submitComment();
		}
	},

	'{submit} click': function(el, event) {

		// prevent user proceed this if the image still under uploading process
		if (self.hasUploadItems()) {
			return;
		}

		if (el.enabled()) {
			self.submitComment();
		}
	},

	'{dismissError} click': function(el, event) {
		self.errorMessage().addClass('t-hidden');
	},

	setMentionsLayout: function() {
		var loader = $.Deferred();
		var editor = self.editor();
		var mentions = editor.controller('mentions');

		if (mentions) {
			mentions.cloneLayout();
			return;
		}

		// Get the immediate parent
		var header = self.editor().parent();

		editor.mentions({
			triggers: {

				"@": {
					type: "entity",
					wrap: false,
					stop: "",
					allowSpace: true,
					finalize: true,
					query: {
						loadingHint: true,
						emptyHint: opts.hints.friends.find('[data-empty]').html(),
						searchHint: opts.hints.friends.find('[data-search]').html(),

						data: function(keyword) {

							var task = $.Deferred();

							EasySocial.ajax("site/controllers/friends/suggest" , {
									search: keyword,
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
							}).fail(task.reject);

							return task;
						},
						use: function(item) {
							return item.type + ":" + item.id;
						}
					}
				},
				"#": {
					"type": "hashtag",
					"wrap": true,
					"stop": " #",
					"allowSpace": false,
					"query": {
						loadingHint: true,
						emptyHint: opts.hints.hashtags.find('[data-empty]').html(),
						searchHint: opts.hints.hashtags.find('[data-search]').html(),
						data: function(keyword) {

							var task = $.Deferred();

							EasySocial.ajax("site/controllers/hashtags/suggest", {search: keyword})
								.done(function(items) {

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
								})
								.fail(task.reject);

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
						data: $.parseJSON(parent.options.emoticons),
						renderAll: true
					}
				}
			},
			plugin: {
				autocomplete: {
					id: "es",
					component: "",
					sticky: true,
					position: {
						my: 'left top',
						at: 'left bottom',
						of: header,
						collision: 'none'
					},
					size: {
						width: function() {
							return header.outerWidth();
						}
					}
				}
			}
		});
	},

	isCommenting: false,

	submitComment: function() {
		var comment = self.input().val();

		self.errorMessage().addClass('t-hidden');

		// If comment value is empty, then don't proceed
		if ($.trim(comment) == '') {

			// Display appropriate message to user
			self.errorMessage().removeClass('t-hidden');
			self.errorCommentMessage().html(parent.options.errorMessage);

			return false;
		}

		if (self.isCommenting) {
			return false;
		}

		self.isCommenting = true;

		// Add loading indicator below the textarea
		// And disable the submit button during processing
		self.submit()
			.addClass('is-loading')
			.attr('disabled');

		// Disable comment form
		self.disableForm();

		// Execute save
		self.save()
			.done(function(comment) {
				// Rather than using commentItem ejs, let PHP return a full block of HTML codes
				// This is to unify 1 single theme file to use loading via static or ajax

				// Trigger parent's commentSaved event
				self.parent.trigger('newCommentSaved', [comment]);

				// Enable the submit button
				self.submit().enabled(true);

				var editor = self.editor();
				var mentions = editor.controller("mentions");

				// Reset the mentions upon saving.
				mentions && mentions.reset();

				// Update the stream exclude id if applicable
				if (self.parent.options.streamid != '') {
					self.updateStreamExcludeIds(self.parent.options.streamid);
				}

			})
			.fail(function(message) {
				self.errorMessage().removeClass('t-hidden');
				self.errorCommentMessage().html(message);

				// Enable the submit button
				self.submit().enabled(true);

				self.submit()
					.removeClass('is-loading')
					.removeAttr('disabled');

				self.enableForm();
			}).always(function() {
				self.isCommenting = false;

				// Initialize reactions
				if (window.es.mobile || window.es.tablet) {
					window.es.initReactions();
				}

			});
	},

	save: function() {
		var mentions = self.editor().controller("mentions");

		var data = {
			url: self.parent.options.url,
			mentions: mentions ? mentions.toArray() : []
		};

		data.mentions = $.map(data.mentions, function(mention){

			if ((mention.type==="hashtag" || mention.type==="emoticon") && $.isPlainObject(mention.value)) {
				mention.value = mention.value.title.slice(1);
			}
			return JSON.stringify(mention);
		});

		return EasySocial.ajax('site/controllers/comments/save', {
			uid: self.parent.options.uid,
			element: self.parent.options.element,
			group: self.parent.options.group,
			verb: self.parent.options.verb,
			streamid: self.parent.options.streamid,
			input: self.input().val(),
			attachmentIds: opts.attachmentIds,
			data: data,
			clusterid: self.parent.options.clusterid,
			postActor: self.getPostActor()
		});
	},

	getPostActor: function() {
		var postAsHidden = $('[data-postas-base] [data-postas-hidden]');

		postActor = postAsHidden.length > 0 ? postAsHidden.val() : 'user';

		return postActor;
	},

	updateStreamExcludeIds: function(id) {
		// ids = self.element.data('excludeids' );
		ids = $('[data-streams-wrapper]').data( 'excludeids' );

		newIds = '';

		if (ids != '' && ids != undefined) {
			newIds = ids + ',' + id;
		} else {
			newIds = id;
		}

		$('[data-streams-wrapper]').data('excludeids', newIds);
	},

	disableForm: function() {
		// Disable input
		self.input().attr('disabled', true);
		self.submit().attr('disabled', true);

		// Disable submit button
		self.submit().disabled(true);
	},

	enableForm: function() {
		// Enable and reset input
		self.input().removeAttr('disabled');
		self.submit().removeAttr('disabled');

		// Enable submit button
		self.submit().enabled(true);
	},

	'{parent} newCommentSaved': function() {
		self.submit()
			.removeClass('is-loading');

		// Enable comment form
		self.enableForm();

		// Reset the attachments
		opts.attachmentIds = [];

		// Get all the attachment items in the queue
		var attachmentItems = self.attachmentItem.inside(self.attachmentQueue.selector);
		attachmentItems.remove();

		self.attachmentQueue().removeClass('has-attachments');

		// Reset comment input
		self.input().val('');
	}
}});


module.resolve();

});
