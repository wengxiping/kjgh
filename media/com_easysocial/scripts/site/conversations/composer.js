EasySocial.module( 'site/conversations/composer' , function($){

var module 	= this;

EasySocial.require()
.library('mentions')
.script('site/friends/suggest', 'uploader/uploader')
.done(function($){

EasySocial.Controller('Conversations.Composer', {
	defaultOptions: {

		draftMessage: {},
		emoticons: [],

		// Determines if these features should be enabled.
		"attachments": true,
		"location": true,
		"showNonFriend": false,
		"isNewPage": false,

		// Uploader properties.
		"extensionsAllowed": "",

		// File uploads
		"{uploader}": "[data-composer-attachment]",
		"{uploaderQueueItem}": "[data-uploaderQueue-item]",

		// Mass Conversation.
		"{massConversation}": "[data-mass-conversation-checkbox]",

		// Location service.
		"{location}": "[data-composer-location]",

		// The text editor.
		"{editorHeader}": "[data-composer-editor-header]",
		"{editorArea}": "[data-composer-editor-area]",
		"{editor}": "[data-composer-editor]",

		// Wrapper for suggest to work.
		"{friendSuggest}": "[data-friends-suggest]",

		"{recipients}": "input[name=uid\\[\\]],input[name=list_id\\[\\]]",

		"{recipientRow}": "[data-composer-recipients]",
		"{messageRow}": "[data-composer-message]",

		// submit button
		"{submit}" : "[data-composer-submit]",
	}
}, function(self, opts) { return {

	init: function() {

		// Initialize the participants textbox.
		self.initSuggest();

		// Initialize uploader
		if (opts.attachments) {
			self.initUploader();
		}

		// Since the page just initialized, we just update it with not typing
		if (!opts.isNewPage) {
			self.setTyping(0);
		}

		self.setMentionsLayout();

		if (!opts.isNewPage) {
			window.onbeforeunload = function() {
				self.setTyping(0);
			};
		}

		if (self.parent !== undefined) {
			self.options.emoticons = self.parent.options.emoticons
		}
	},

	initUploader: function() {
		// Implement uploader controller.
		self.uploader().implement(EasySocial.Controller.Uploader, {
			"temporaryUpload": true,
			"query": "type=conversations",
			"type": 'conversations',
			"extensionsAllowed": opts.extensionsAllowed
		});
	},

	initSuggest: function() {
		self.friendSuggest()
			.addController(EasySocial.Controller.Friends.Suggest, {
					friendList: true,
					friendListName: "list_id[]",
					showNonFriend: opts.showNonFriend,
					privacyRule: 'profiles.post.message',
					type: 'conversations'
				});
	},

	initEditor: function() {
		self.editor().expandingTextarea();
	},

	setTyping: function(typing) {
		var state = typing === true ? 1 : 0;

		$.ajax({
			"url": window.es.rootUrl + '/components/com_easysocial/polling.php',
			"method": "post",
			"data": {
				"method": "typing",
				"userId": self.parent.options.userId,
				"typing": state,
				"key": self.parent.options.userKey,
				"conversationId": self.parent.options.conversationId
			}
		});
	},

	resetForm: function() {
		self.editor().val('');
	},

	typingTimer: null,

	"{submit} click": function(el) {
		var tagsArray = self.editorArea().mentions('controller').toArray();
		var tags = $.map(tagsArray, function(mention){
			if (mention.type==="emoticon" && $.isPlainObject(mention.value)) {
				mention.value = mention.value.title.slice(1);
			}
			return mention;
		});

		if (tags) {
			$.each(tags, function(idx, tag) {
				$.each(tag, function(key, element) {
					$('<input>').attr({
						type: 'hidden',
						name: 'tags[' + idx + '][' + key + ']',
						value: element
					}).appendTo('[data-composer-form]');
				});
			})
		}
	},

	"{massConversation} change": function(el) {
		if (el.is(':checked')) {
			self.recipientRow().hide();
			return;
		}

		self.recipientRow().show();
	},

	// Capture ctrl + enter / cmd + enter submission
	"{editor} keydown": function(editor, event) {

		if (opts.isNewPage) {
			return;
		}

		if ((event.metaKey || event.ctrlKey || self.parent.options.enterToSubmit) && event.keyCode == 13) {
			self.trigger('save');
			event.preventDefault();

			return;
		}

		if (!self.parent.options.typingState || opts.isNewPage) {
			return;
		}

		self.setTyping(true);

		// Update the typing state
		clearTimeout(self.typingTimer);

		self.typingTimer = setTimeout(self.setTyping, 1000);
	},

	// When a new item is added, we want to disable reply button and reenable it when the upload finished
	"{uploader} FilesAdded": function(el, event, uploader, files) {
		self.parent.toggleReplyButton(true);
	},

	"{uploader} FileUploaded": function(el, event, uploader, files) {

		// If there is no more file in queue, we re-enable the reply button
		if ($('[data-uploaderqueue]').children('.is-queue').length == 0) {
			self.parent.toggleReplyButton(false);
		}

	},

	// Uploader
	"{uploaderQueueItem} FileUploaded": function(element, event, file, response) {

		var state = element.find('[data-upload-state]');

		// Once it is uploaded, removed the uploading state
		state.remove();
	},

	"{uploaderQueueItem} FileError": function(element, event, file, response) {
		var state = element.find('[data-upload-state]');
		var message = state.data('error');

		state.html(message)
			.removeClass('t-text--success')
			.addClass('t-text--danger');
	},

	saveDraftMessage: function() {
		// Get current conversation id
		var id = self.parent.replyForm().data('id');

		opts.draftMessage[id] = self.editor().val();
	},

	setDraftMessage: function(id) {
		var message = opts.draftMessage[id];

		self.editor().val(message);
	},

	setMentionsLayout: function() {

		var editor = self.editorArea();
		var mentions = editor.controller("mentions");

		if (mentions) {
			mentions.cloneLayout();
			return;
		}

		var header = self.editorHeader();

		editor
			.mentions({

				triggers: {

					"@": {
						type : "entity",
						wrap : false,
						stop : "",
						allowSpace : true,
						finalize : true,
						query:
						{
							loadingHint	: true,
							searchHint	: $('[data-hints-friends]').find('[data-search]').html(),
							emptyHint	: $('[data-hints-friends]').find('[data-empty]').html(),

							data: function(keyword) {

								var task = $.Deferred();

								EasySocial.ajax("site/controllers/friends/suggest" , { search: keyword })
								.done(function(items) {
									if (!$.isArray(items)) task.reject();

									var items = $.map(items, function(item)
									{
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
					"#":
					{
						type : "hashtag",
						wrap : true,
						stop : " #",
						allowSpace : false,
						query:
						{
							loadingHint : false,
							searchHint : $('[data-hints-friends]').find('[data-search]').html(),
							emptyHint : $('[data-hints-friends]').find('[data-empty]').html(),
							data: function(keyword)
							{
								var task = $.Deferred();

								EasySocial.ajax("site/controllers/hashtags/suggest", {search: keyword})
									.done(function(items)
									{
										if (!$.isArray(items)) task.reject();

										var items = $.map(items, function(item){

											var html = $('<div/>').html(item);
											var title = html.find('[data-suggest-title]').val();
											var id = html.find('[data-suggest-id]').val();

											return {
												"id": id,
												"title": "#" + title,
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
							searchHint: $('[data-hints-emoticons]').find('[data-search]').html(),
							emptyHint: $('[data-hints-emoticons]').find('[data-empty]').html(),
							data: $.parseJSON(self.options.emoticons),
							renderAll: true
						}
					}
				},
				plugin:
				{
					autocomplete:
					{
						id : "es",
						component : "",
						sticky: true,
						shadow: true,
						position:
						{
							my: 'left top',
							at: 'left bottom',
							of: header,
							collision: 'none'
						},
						size:
						{
							width: function()
							{
								return header.width();
							}
						}
					}
				}
			});
	}
}});

module.resolve();
});

});

