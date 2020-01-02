EasySocial.module('site/audios/form', function($) {

var module = this;

EasySocial.require()
.script('site/friends/suggest', 'site/albums/uploader')
.library('mentions')
.done(function($) {

EasySocial.Controller('Audios.Form', {
	defaultOptions: {
		defaultAlbumart: null,
		importMetadata: false,

		"{audioSource}": "[data-audio-source]",
		"{albumartSource}": "[data-albumart-source]",
		"{form}": "[data-audios-form]",
		"{albumartForm}": "[data-form-albumart]",
		"{albumartFrame}": "[data-albumart-frame]",
		"{removeButton}": "[data-albumart-remove-button]",

		// Forms for audio source
		"{forms}": "[data-form-source]",
		"{linkForm}": "[data-form-link]",
		"{uploadForm}": "[data-form-upload]",
		"{file}": "[data-audio-file]",
		"{linkSource}": "[data-audio-link]",
		"{albumArt}": "[data-audio-albumart]",
		"{albumartData}": "[data-audio-albumart-data]",
		"{browseButton}": "[data-browse-button]",

		// Mentions
		"{mentions}": "[data-mentions]",
		"{hashtags}": "[data-hashtags]",
		"{header}": "[data-hashtags-header]",

		// Fields
		"{title}": "[data-audio-title]",
		"{desc}": "[data-audio-desc]",
		"{artist}": "[data-audio-artist]",
		"{album}": "[data-audio-album]",
		"{filename}": "[data-audio-filename]",
		"{albumartFilename}": "[data-audio-albumart-filename]",

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

	'{file} change' : function(el , event) {

		if($.isEmpty(el.val())) {
			return;
		}

		var label = el.val().replace(/\\/g, '/').replace(/.*\//, '');

		el.parents('.o-input-group').find(':text').val(label);

		if (self.options.importMetadata === '0') {
			return;
		}

		self.browseButton().addClass('is-loading');

		EasySocial.ajax('site/controllers/audios/importMetadata' , {
			files: el
		}, {
			type: 'iframe'
		}).done(function(result){

			// Once get the metadata, we fill in the field
			if (result['title']) {
				self.title().val(result['title'])
			}

			if (result['description']) {
				self.desc().val(result['description'])
			}

			if (result['artist']) {
				self.artist().val(result['artist'])
			}

			if (result['album']) {
				self.album().val(result['album'])
			}

			self.browseButton().removeClass('is-loading');

		}).fail(function(msg) {

		});
	},

	'{linkSource} focusout' : function(el, ev) {

		var url = el.val();

		if (url.length == 0) {
			return;
		}

		self.linkForm().addClass('is-loading');

		self.clearError(self.linkForm());

		EasySocial.ajax('site/controllers/audios/processLink', {
			"link": url
		}).done(function(result) {

			if (result['title']) {
				self.title().val(result['title'])
			}

			if (result['description']) {
				self.desc().val(result['description'])
			}

			if (result['artist']) {
				self.artist().val(result['artist'])
			}

			if (result['album']) {
				self.album().val(result['album'])
			}

			self.linkForm().removeClass('is-loading');

		}).fail(function(message){
			self.linkForm().removeClass('is-loading');

			self.showError(self.linkForm(), message);
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

	'{albumArt} change' : function(el , event) {
		if($.isEmpty(el.val())) {
			return;
		}

		var label = el.val().replace(/\\/g, '/').replace(/.*\//, '');

		el.parents('.o-input-group').find(':text').val(label);

		self.albumartFrame().addClass('is-loading');

		EasySocial.ajax('site/controllers/audios/uploadAlbumArt' , {
			files: el
		}, {
			type: 'iframe'
		}).done(function(result){

			self.albumartFrame().removeClass('is-loading');

			var resultString = JSON.stringify(result);

			// Set the result in a string format
			self.albumartData().val(resultString);

			self.setAlbumArt(result.thumbnail.uri);

			self.removeButton().show();

		}).fail(function(msg) {

			self.albumartFrame().removeClass('is-loading');
		});
	},

	setAlbumArt: function(url, position) {

		self.albumartFrame()
			.css({
				backgroundImage: $.cssUrl(url)
			});
	},

	"{removeButton} click": function(el) {
		self.albumartData().val();

		self.setAlbumArt(self.options.defaultAlbumart);

		el.parents('.input-group').find(':text').val('');
		self.albumartFilename().val('');

		self.albumartData().val('delete');

		el.hide();
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

	"{audioSource} change": function(audioSource, event) {

		var source = $(audioSource).val();

		var form = self[source + "Form"]();

		// Hide all source forms
		self.forms().addClass('t-hidden');

		// Remove hidden class for the active form
		form.removeClass('t-hidden');
	},

	"{albumartSource} change": function(albumartSource, event) {
		var checked = albumartSource.is(':checked');
		$('[data-albumart-input]').attr('disabled', checked);
		$('[data-audio-albumart]').attr('disabled', checked);

		if (checked) {
			$('[data-audio-albumart-source]').val('audio');
		} else {
			$('[data-audio-albumart-source]').val('upload');
		}

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

							EasySocial.ajax("site/controllers/hashtags/suggest", {search: keyword, type: "audio"})
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

	"{saveButton} click": function(ele, event) {

		var title = self.title().val();
		var wrapper = self.title().closest('div.o-control-input');
		title = title.trim();

		// clear the error 1st
		wrapper.removeClass('has-error');

		if (title == '') {
			wrapper.addClass('has-error');
			self.title().focus();
			return false;
		}

		return true;
	}
}});

module.resolve();

});
});
