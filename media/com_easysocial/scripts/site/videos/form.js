EasySocial.module('site/videos/form', function($) {

var module = this;

EasySocial.require()
.script('site/friends/suggest')
.library('mentions')
.done(function($) {

EasySocial.Controller('Videos.Form', {
	defaultOptions: {
		"{videoSource}": "[data-video-source]",

		// Forms for video source
		"{forms}": "[data-form-source]",
		"{linkForm}": "[data-form-link]",
		"{uploadForm}": "[data-form-upload]",
		"{linkSource}": "[data-video-link]",

		// Mentions
		"{mentions}": "[data-mentions]",
		'{hashtags}': '[data-hashtags]',
		"{header}": "[data-hashtags-header]",
		"{file}": "[data-video-file]",
		"{browseButton}": "[data-browse-button]",
		"{videoId}": "[data-video-id]",
		"{fileUploaded}": "[data-file-uploaded]",
		"{videoTitle}": "[data-video-title]",
		"{videoDescription}": "[data-video-desc]",
		"{saveButton}": "[data-save-button]"
	}
}, function(self, opts, base) { return {

	init: function() {
		self.initMentions();

		// Get available hints for friend suggestions and hashtags
		opts.hints = {
				"friends": self.element.find('[data-hints-friends]'),
				"hashtags": self.element.find('[data-hints-hashtags]')
		};

		// Apply the mentions on the comment form
		self.setMentionsLayout();
	},

	initMentions: function() {

		var options = {
				"showNonFriend": false,
				"includeSelf": true,
				"name": "tags[]",
				"exclusion": opts.tagsExclusion
			}

		if (opts.isPrivateCluster) {
			var options = $.extend(options, {
				clusterId: opts.uid,
				clusterType: opts.type
			});
		}

		self.mentions()
			.addController("EasySocial.Controller.Friends.Suggest", options);
	},

	'{file} change' : function(el , event) {

		if($.isEmpty(el.val())) {
			return;
		}

		var label = el.val().replace(/\\/g, '/').replace(/.*\//, '');

		el.parents('.o-input-group').find(':text').val(opts.uploadingText + '...');

		self.clearError(self.uploadForm());
		self.browseButton().addClass('is-loading');
		self.saveButton().attr('disabled', true);

		var isEditing = false;

		if (self.videoId().val()) {
			isEditing = self.videoId().val();
		}

		EasySocial.ajax('site/controllers/videos/uploadFile' , {
			files: el,
			type: opts.type,
			uid: opts.uid,
			isEditing: isEditing
		}, {
			type: 'iframe'
		}).done(function(result){

			self.saveButton().attr('disabled', false);

			self.videoId().val(result.id);
			self.fileUploaded().val(true);

			if (!self.videoTitle().val()) {
				self.videoTitle().val(result.title);
			}

			el.parents('.o-input-group').find(':text').val(label);

			self.browseButton().removeClass('is-loading');

			// Reset the file input to avoid the form sending the data
			el.val('');

		}).fail(function(msg) {

			self.showError(self.uploadForm(), msg);
			el.parents('.o-input-group').find(':text').val(label);

			self.browseButton().removeClass('is-loading');
		});
	},

	showError: function (field, message) {
		field.addClass('has-error');
		field.find('[data-link-notice]').html(message);
	},

	clearError: function (field) {
		field.removeClass('has-error');
		field.find('[data-link-notice]').html('');
	},

	"{videoSource} change": function(videoSource, event) {

		var source = $(videoSource).val();
		var form = self[source + "Form"]();

		// Hide all source forms
		self.forms().addClass('t-hidden');

		// Remove hidden class for the active form
		form.removeClass('t-hidden');
	},

	setMentionsLayout: function() {
		var hashtags = self.hashtags();
		var mentions = hashtags.controller("mentions");

		if (mentions) {
			mentions.cloneLayout();
			return;
		}

		var header = self.header();

		hashtags.mentions({

			triggers: {
				"#": {
					"type": "hashtag",
					"wrap": true,
					"stop": " #",
					"allowSpace": false,
					"query": {
						"loadingHint": false,
						"searchHint": opts.hints.hashtags.find('[data-search]'),
						"emptyHint": opts.hints.hashtags.find('[data-empty]'),
						data: function(keyword) {

							var task = $.Deferred();

							EasySocial.ajax("site/controllers/hashtags/suggest", {search: keyword, type: "video"})
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
				}
			},
			"plugin": {
					"autocomplete": {
						"id": "es",
						"component": "",
						"position": {
							my: 'left top',
							at: 'left bottom',
							of: header.parent(),
							collision: 'none'
						},
						"size": {
							width: function() {
								return header.parent().outerWidth();
							}
						}
					}
				}

		});
	},

	"{linkSource} focusout": function(el){
		var defaultEditor = $('[data-video-desc-field]').data('video-editor-type');

        // If they choose editors, do not proceed.
        if (defaultEditor != 'noeditor') {
            return;
        }

		var url = el.val();

		if (url.length < 1) {
			return;
		}

		self.linkForm().addClass('is-loading');

		self.clearError(self.linkForm());

		EasySocial.ajax('site/controllers/videos/processLink', {
			"link" : url
		}).done(function(data){
			if (data['title']) {
				self.videoTitle().val(data['title']);
			}

			if (data['description']) {
				self.videoDescription().val(data['description']);
			}

			self.linkForm().removeClass('is-loading');
		}).fail(function(message){
			self.linkForm().removeClass('is-loading');
			self.showError(self.linkForm(), message);
		});
	},

	"{saveButton} click": function(ele, event) {

		var title = self.videoTitle().val();
		var wrapper = self.videoTitle().closest('div.o-control-input');
		title = title.trim();

		// clear the error 1st
		wrapper.removeClass('has-error');

		if (title == '') {
			wrapper.addClass('has-error');
			self.videoTitle().focus();
			return false;
		}

		return true;
	}
}});

module.resolve();

});
});
