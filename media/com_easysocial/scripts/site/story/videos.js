EasySocial.module("site/story/videos", function($){

var module = this;

EasySocial.require()
.library('image', 'plupload')
.done(function($){

EasySocial.Controller("Story.Videos", {
	defaultOptions: {
		// This is the main wrapper for the form
		"{form}": "[data-video-form]",

		// This is video panel button
		"{panelButton}" : '[data-story-plugin-name="videos"]',

		// Video links
		"{insertVideo}": "[data-insert-video]",
		"{videoLink}": "[data-video-link]",
		"{videoCategory}": "[data-video-category]",

		// Video uploads
		"{uploaderForm}": "[data-video-uploader]",
		"{uploaderButton}": "[data-video-uploader-button]",
		"{uploaderDropsite}": "[data-video-uploader-dropsite]",
		"{uploaderProgressBar}": "[data-video-uploader-progress-bar]",
		"{uploaderProgressText}": "[data-video-uploader-progress-text]",

		"{uploaderUploadBar}": "[data-video-uploader-upload-bar]",
		"{uploaderUploadText}": "[data-video-uploader-upload-text]",

		// Video preview
		"{removeButton}": "[data-remove-video]",
		"{previewImageWrapper}": "[data-video-preview-image]",
		"{previewTitle}": "[data-video-preview-title]",
		"{title}": "[data-video-title]",
		"{previewDescription}": "[data-video-preview-description]",
		"{description}": "[data-video-description]"
	}
}, function(self, opts, base) { return {

	init: function() {

		// If video uploader form doesn't exist, perhaps the admin already disabled this
		if (self.uploaderForm().length == 0 && self.videoLink().length == 0) {
			return;
		}

		if (self.uploaderForm().length > 0) {
			self.uploader = self.uploaderForm().addController("plupload", $.extend({
					"{uploadButton}": self.uploaderButton.selector,
					"{uploadDropsite}": self.uploaderDropsite.selector
				}, opts.uploader)
			);

			self.plupload = self.uploader.plupload;
		}

		if (opts.isEdit) {
			self.video = {
				"type": opts.video.source,
				"title": opts.video.title,
				"description": self.description().val(),
				"link": opts.video.link,
				"id": opts.video.id,
				"isEncoding": opts.video.isEncoding
			};
		}
	},

	renderDefaultPlaceholderDescription: false,
	clickedRemoveButton: false,

	isProcessed: function() {
		self.form().switchClass('is-processed');

		self.processing = false;
	},

	isUploading: function() {
		self.form().switchClass('is-uploading');
	},

	isProcessing: function() {
		self.form().switchClass('is-processing');

		self.processing = true;
	},

	isEncoding: function() {
		self.form().switchClass('is-encoding');
	},

	isInitial: function() {
		self.form().switchClass('is-waiting');
	},

	currentCategory: null,
	processing: false,
	video: null,
	videoType: null,

	updatePreview: function(type, data, imageUrl) {

		self.video = {
			"type": type,
			"title": data.title,
			"description": data.description,
			"link": data.link,
			"id": data.id ? data.id : '',
			"isEncoding": false
		};

		// Retrieve the placeholder value
		title = self.title().attr("placeholder");
		description = self.description().attr("placeholder");

		if ($.trim(data.title) != '') {
			title = data.title;
			self.title().val(title);
		}

		if ($.trim(data.description) != '') {
			description = data.description;
			self.description().val(description);

            self.previewDescription().removeClass("no-description");
		}

		self.previewTitle().html(title);
		self.previewDescription().html(description);

		// Load the image
		$.Image.get(imageUrl).done(function(image){
			image.appendTo(self.previewImageWrapper());
		});

	},

	resetProgress: function() {

		// Reset the progress bar
		self.uploaderProgressBar().css('width', '0%');
		self.uploaderProgressText().html('0%');
	},

	clearForm: function(resetVideo) {

		if (resetVideo) {
			self.video = null;
		}

		// Set to initial position
		self.isInitial();

		// Reset all the form values
		self.videoLink().val('');

		self.previewImageWrapper().empty();

		self.previewTitle().empty();
		self.title().val('');

		self.previewDescription().empty();
		self.description().val('');
	},

	editTitleEvent: "click.es.story.video.editLinkTitle",
	editDescriptionEvent: "click.es.story.video.editLinkDescription",

	editTitle: function() {

		// Apply the class to the form wrapper
        self.form().addClass('editing-title');

		setTimeout(function(){

			self.title()
				.val(self.previewTitle().text())
				.focus()[0]
				.select();

			// Save the title when there is changes in the textbox. #3321
			$(self.title()).on('change keyup paste', function() {
				self.saveTitle("apply");
			});

			$(document).on(self.editTitleEvent, function(event) {

				if (event.target !== self.title()[0]) {
					self.saveTitle("save");
				}
			});

		}, 1);
	},

	saveTitle: function(operation) {

		if (!operation) {
			operation = 'save';
		}

		var value = self.title().val();

		// Changing the title from the textbox
		if (operation == 'apply') {
			self.video.title = value;
			return;
		}

		// Ensure that the title field has value.
		if (value.length > 0 && $.trim(value) != "") {
			if (operation == 'save') {
				self.previewTitle().html(value);
			}

			self.video.title = value;
		}

		// Remove the editing title class
        self.form().removeClass('editing-title');

		$(document).off(self.editTitleEvent);
	},

	checkVideoStatus: function(videoId, percentage) {
		EasySocial.ajax('site/controllers/videos/status', {
			"id": videoId,
			"uid": opts.video.uid,
			"type": opts.video.type,
			"createStream": 0,
			"percentage": percentage,
			"unpublished": 1
		}).done(function(permalink, percent, data, thumbnail) {

			if (percent === 'done') {

				self.processing = false;

				// Set the progress bar to 100%
				self.uploaderProgressBar().css('width', '100%');
				self.uploaderProgressText().html('100%');

				// Update the state
				self.isProcessed();

				// Update the preview
				self.updatePreview('upload', data, thumbnail);

				// Reset the progress bar
				self.resetProgress();

				return;
			}

			// There is a possibility that the progress is throwing errors on the line so we should skip this
			if (percent == 'ignore') {
				self.checkVideoStatus(videoId, percentage);
				return;
			}

			// Set the progress bar width
			var progress = percent + '%';
			self.uploaderProgressBar().css('width', progress);
			self.uploaderProgressText().html(progress);

			// This should run in a loop
			self.checkVideoStatus(videoId, percent);
		});
	},

	editDescription: function() {

        self.form().addClass('editing-description');

		setTimeout(function(){

			var descriptionClone = self.previewDescription().clone();
			var noDescription = descriptionClone.hasClass("no-description");

			descriptionClone.wrapInner(self.description());

			var previewDescriptionText = self.previewDescription().text();

			if (opts.isEdit && noDescription) {

				self.description()
					.val("")
					.focus()[0].select();

			} else if (noDescription) {
				self.description()
					.val("")
					.focus()[0].select();

			} else {
				self.description()
					.val(previewDescriptionText)
					.focus()[0].select();
			}

			// Save the description when there is changes in the textbox. #819
			$(self.description()).on('change keyup paste', function() {
				self.saveDescription("apply");
			});

			$(document).on(self.editDescriptionEvent, function(event) {

				if (event.target !== self.description()[0]) {
					self.saveDescription("save");
				}
			});
		}, 1);
	},

	saveDescription: function(operation) {

		// Do not proceed this if doesn't have data for this
		if (!self.video) {
			return;
		}

		if (!operation) {
			operation = 'save';
		}

		// var value = self.description().val().replace(/\n/g, "<br>");
		var value = self.description().val();

		// Always set this flag to false here so it will re-calculate again for different operation
		self.renderDefaultPlaceholderDescription = false;

		switch (operation) {

			case "save":

				// trim the space first before do validation
				// just in case those user only type space in description
				var noValue = ($.trim(value) === "");

				self.previewDescription()
					.toggleClass("no-description", noValue);

				if (noValue) {
					value = self.description().attr("placeholder");

					// Determine currently render the placeholder description now
					self.renderDefaultPlaceholderDescription = true;
				}

				// Only process this if there got the value for the description
				if (value) {
					self.previewDescription()
						.html(value.replace(/\n/g, "<br>"));
				}

				self.video.description = value;

				self.form().find(".textareaClone").remove();

                self.form().removeClass("editing-description");

				$(document).off(self.editDescriptionEvent);
				break;
			case "apply":

				// Determine currently whether got value for the description or not
				if (value === "") {
					self.renderDefaultPlaceholderDescription = true;
				}

				self.video.description = value;
			case "revert":
				break;
		}
	},

	"{window} easysocial.story.video.panel.insertvideolink" : function(el, ev, url) {

		if (self.video || self.processing || !url) {
			return;
		}

		// Switch to video panel
		self.panelButton().click();

		// Clear up any data inside the form
		self.clearForm(true);

		// Append the video link
		self.videoLink().val(url);

		// Process video link
		self.insertVideo().click();
	},

	"{uploaderForm} FilesAdded": function() {

		// Set the state to uploading
		self.isUploading();

		// Start the upload
		self.plupload.start();
	},

	"{uploaderForm} UploadProgress": function(el, event, uploader, file) {
		// Set the progress bar width
		var progress = file.percent + '%';

		self.uploaderUploadBar().css('width', progress);
		self.uploaderUploadText().html(progress);

	},

	"{uploaderForm} FileUploaded": function(uploaderForm, event, uploader, file, response) {

		// Server thrown an error
		if (response.error) {

			// Set the message
			self.clearMessage();
			self.setMessage(response.error);

			// Display the video upload form again
			self.clearForm(true);

			return false;
		}

		// If the server isn't encoding on the fly, we should display some message
		if (!response.isEncoding) {

			self.processing = false;

			// Set the progress bar to 100%
			self.uploaderProgressBar().css('width', '100%');
			self.uploaderProgressText().html('100%');

			// Update the state
			self.isProcessed();

			// Update the preview
			self.updatePreview('upload', response.data, response.thumbnail);

			self.video.isEncoding = true;

			// Reset the progress bar
			self.resetProgress();

			return;
		}

		// Set status to encoding
		self.isEncoding();

		self.processing = true;

		// Update the progress since the video needs to be converted.
		self.checkVideoStatus(response.data.id, 0);
	},

	"{uploaderForm} Error": function(el, event, uploader, error) {

		// Get the error message
		var message = opts.errors[error.code];

		self.story.setMessage(message, "error");
	},

	"{previewTitle} click": function() {

		var editing = self.form().hasClass('editing-title');

		self.form().toggleClass('editing-title', !editing);

		if (!editing) {
			self.editTitle();
		}
	},

	"{previewDescription} click": function() {
		var editing = self.form().hasClass('editing-description');

		self.form().toggleClass('editing-description', !editing);

		// Do not execute this if that is not editing and clicked remove button from the form
		if (!editing && !self.clickedRemoveButton) {
			self.editDescription();
		}
	},

	"{videoCategory} change": function(videoCategory) {
		self.currentCategory = videoCategory.val();
	},

	"{videoLink} paste": function() {
		setTimeout(function() {
			self.insertVideo().click();
		}, 100);
	},

	"{insertVideo} click": function() {

		var url = self.videoLink().val();

		if (!url || self.processing) {
			return;
		}

		// Hide the form
		self.isProcessing();

		EasySocial.ajax('ajax:/apps/user/videos/controllers/process/process', {
			"type": "link",
			"link": url
		}).done(function(data, image, embed) {
			self.isProcessed();

			data.link = url;

			self.updatePreview('link', data, image);
		}).fail(function(message){

			self.isProcessed();

			self.clearForm(true);

			self.story.setMessage(message, "error");
		});
	},

	"{removeButton} click": function(removeButton) {

		// set this flag to true because use to determine that no need to go through the saveDescription process
		self.clickedRemoveButton = true;

		self.clearForm(true);

		// set this flag to false back after clear the form
		self.clickedRemoveButton = false;
		self.renderDefaultPlaceholderDescription = false;
	},

	//
	// Saving
	//

	"{story} save": function(element, event, save) {

		if (save.currentPanel != 'videos') {
			return;
		}
		var url = self.videoLink().val();

		self.saveTitle();

		// If uploading a video link
		if (url && !self.video) {
			save.reject(opts.errors.messages.insert);
			return;
		}

		// If sharing a video without link and upload
		if (!url && !self.video) {
			save.reject(opts.errors.messages.empty);
			return;
		}

		// If sharing a video without title.
		if (!self.video.title || $.trim(self.video.title) == "") {
			save.reject(opts.errors.messages.title);
			return;
		}

		// Add the task for uploading video
		self.uploadingVideo = save.addTask("uploadingVideo");

		self.save(save);
	},

	"{story} afterSubmit": function() {

		var uploadingVideo = self.uploadingVideo;

		if (!uploadingVideo) {
			return;
		}

		// Reset the form upon submission
		self.clearForm(true);

		delete self.uploadingVideo;

		if (self.video && self.video.isEncoding) {

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/videos/showEncodingMessage')
			});

			delete self.video;
			return;
		}

		delete self.video;
	},

	save: function(save) {

		var uploadingVideo = self.uploadingVideo;

		if (!uploadingVideo) {
			return;
		}

		if (self.processing) {
			save.reject(opts.errors.messages.processing);
			return;
		}

		// Attach the category to the video data
		self.video.category = self.videoCategory().val();

		if (!self.video.category || self.video.category == 0) {
			save.reject(opts.errors.messages.category);
			return;
		}

		if (self.title().val() == '') {
			save.reject(opts.errors.messages.title);
			return;
		}

		// Set the description to empty if detected currently render the default placeholder description before save
		if (self.renderDefaultPlaceholderDescription) {
			self.video.description = '';
		}

		save.addData(self, self.video);

		uploadingVideo.resolve();

		self.videoType = self.video.type;
	},

	"{story} clear": function() {
		self.clearForm(false);
		self.renderDefaultPlaceholderDescription = false;
	}
}});

// Resolve module
module.resolve();

});

});
