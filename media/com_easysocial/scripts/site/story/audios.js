EasySocial.module("site/story/audios", function($){

var module = this;

EasySocial.require()
.library('image', 'plupload')
.done(function($){

EasySocial.Controller("Story.Audios", {
	defaultOptions: {
		// This is the main wrapper for the form
		"{form}": "[data-audio-form]",

		// This is audio panel button
		"{panelButton}": '[data-story-plugin-name="audios"]',

		// Audio links
		"{insertAudio}": "[data-insert-audio]",
		"{audioLink}": "[data-audio-link]",
		"{audioGenre}": "[data-audio-genre]",

		// Audio uploads
		"{uploaderForm}": "[data-audio-uploader]",
		"{uploaderButton}": "[data-audio-uploader-button]",
		"{uploaderDropsite}": "[data-audio-uploader-dropsite]",
		"{uploaderProgressBar}": "[data-audio-uploader-progress-bar]",
		"{uploaderProgressText}": "[data-audio-uploader-progress-text]",

		"{uploaderUploadBar}": "[data-audio-uploader-upload-bar]",
		"{uploaderUploadText}": "[data-audio-uploader-upload-text]",

		// Audio preview
		"{removeButton}": "[data-remove-audio]",
		"{previewImageWrapper}": "[data-audio-preview-image]",
		"{previewTitle}": "[data-audio-preview-title]",
		"{title}": "[data-audio-title]",
		"{previewDescription}": "[data-audio-preview-description]",
		"{description}": "[data-audio-description]",
		"{previewArtist}": "[data-audio-preview-artist]",
		"{artist}": "[data-audio-artist]",
		"{previewAlbum}": "[data-audio-preview-album]",
		"{album}": "[data-audio-album]"
	}
}, function(self, opts, base) { return {

	init: function() {
		// If audio uploader form doesn't exist, perhaps the admin already disabled this
		if (self.uploaderForm().length == 0 && self.audioLink().length == 0) {
			return;
		}

		// Only implement uploader if the upload form exists
		if (self.uploaderForm().length > 0) {
			self.uploader = self.uploaderForm().addController("plupload", $.extend({
					"{uploadButton}": self.uploaderButton.selector,
					"{uploadDropsite}": self.uploaderDropsite.selector
				}, opts.uploader)
			);

			self.plupload = self.uploader.plupload;
		}

		if (opts.isEdit) {
			self.audio = {
				"type": opts.audio.source,
				"title": opts.audio.title,
				"artist": opts.audio.artist,
				"album": opts.audio.album,
				"description": opts.audio.description,
				"link": opts.audio.link,
				"id": opts.audio.id,
				"isEncoding": opts.audio.isEncoding
			};
		}
	},

	renderDefaultPlaceholderArtist: false,
	renderDefaultPlaceholderAlbum: false,
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

	currentGenre: null,
	processing: false,
	audio: null,
	audioType: null,

	updatePreview: function(type, data, imageUrl) {

		self.audio = {
			"type": type,
			"title": data.title,
			"artist": data.artist,
			"album": data.album,
			"description": data.description,
			"link": data.link,
			"id": data.id ? data.id : '',
			"isEncoding": false
		};

		// Retrieve placeholder value for each of the field from the form
		titleContent = self.title().attr("placeholder");
		artistContent = self.artist().attr("placeholder");
		albumContent = self.album().attr("placeholder");
		descriptionContent = self.description().attr("placeholder");

		if ($.trim(data.title) != '') {
			titleContent = data.title;
			self.title().val(titleContent);
		}

		if ($.trim(data.artist) != '') {
			artistContent = data.artist;
			self.artist().val(artistContent);
		}

		if ($.trim(data.album) != '') {
			albumContent = data.album;
			self.album().val(albumContent);
		}

		if ($.trim(data.description) != '') {
			descriptionContent = data.description;
			self.description().val(descriptionContent);

			self.previewDescription().removeClass("no-description");
		}

		self.previewTitle().html(titleContent);
		self.previewArtist().html(artistContent);
		self.previewAlbum().html(albumContent);
		self.previewDescription().html(descriptionContent);

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

	clearForm: function(resetAudio) {

		if (resetAudio) {
			self.audio = null;
		}

		// Set to initial position
		self.isInitial();

		// Reset all the form values
		self.audioLink().val('');

		self.previewImageWrapper().empty();

		self.previewTitle().empty();
		self.title().val('');

		self.previewArtist().empty();
		self.artist().val('');

		self.previewAlbum().empty();
		self.album().val('');

		self.previewDescription().empty();
		self.description().val('');
	},

	editArtistEvent: "click.es.story.audio.editLinkArtist",
	editAlbumEvent: "click.es.story.audio.editLinkAlbum",
	editTitleEvent: "click.es.story.audio.editLinkTitle",
	editDescriptionEvent: "click.es.story.audio.editLinkDescription",

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

		// Do not proceed this if doesn't have data for this
		if (!self.audio) {
			return;
		}

		if (!operation) {
			operation = 'save';
		}

		var value = self.title().val();

		// Changing the title from the textbox
		if (operation == 'apply') {
			self.audio.title = value;
			return;
		}

		// Ensure that the title field has value.
		if (value.length > 0 && $.trim(value) != "") {
			if (operation == 'save') {
				self.previewTitle().html(value);
			}

			self.audio.title = value;
		}

		// Remove the editing title class
		self.form().removeClass('editing-title');

		if (self.audio.title == '' && self.previewTitle().html().length > 0) {
			self.audio.title = self.previewTitle().html();
		}

		$(document).off(self.editTitleEvent);
	},

	editArtist: function() {

		// Apply the class to the form wrapper
		self.form().addClass('editing-artist');

		setTimeout(function(){
			self.artist()
				.focus()[0]
				.select();

			// Save the artist when there is changes in the textbox. #3321
			$(self.artist()).on('change keyup paste', function() {
				self.saveArtist("apply");
			});

			$(document).on(self.editArtistEvent, function(event) {

				if (event.target !== self.artist()[0]) {
					self.saveArtist("save");
				}
			});

		}, 1);
	},

	saveArtist: function(operation) {

		// Do not proceed this if doesn't have data for this
		if (!self.audio) {
			return;
		}

		if (!operation) {
			operation = 'save';
		}

		var value = self.artist().val();

		// Changing the title from the textbox
		if (operation == 'apply') {
			self.audio.artist = value;
			return;
		}

		self.renderDefaultPlaceholderArtist = false;

		if (operation == 'save') {

			// trim the space first before do validation
			// just in case those user only type space in artist field
			var noValue = ($.trim(value) === "");

			if (noValue) {

				// Retrieve placeholder value
				value = self.artist().attr("placeholder");

				// Set a flag to determine that currently render back placeholder value
				self.renderDefaultPlaceholderArtist = true;

				// if the field doesn't have any custom content, then only replace to use placeholder value
				if (value) {
					self.previewArtist()
						.html(value.replace(/\n/g, "<br>"));
				}

			} else {

				// Ensure that only has value then only preview it
				self.previewArtist().html(value);
			}
		}

		// Remove the editing artist class
		self.form().removeClass('editing-artist');

		self.audio.artist = value;

		$(document).off(self.editArtistEvent);
	},

	editAlbum: function() {

		// Apply the class to the form wrapper
		self.form().addClass('editing-album');

		setTimeout(function(){

			self.album()
				.focus()[0]
				.select();

			// Save the album when there is changes in the textbox. #3321
			$(self.album()).on('change keyup paste', function() {
				self.saveAlbum("apply");
			});

			$(document).on(self.editAlbumEvent, function(event) {

				if (event.target !== self.album()[0]) {
					self.saveAlbum("save");
				}
			});

		}, 1);
	},

	saveAlbum: function(operation) {

		// Do not proceed this if doesn't have data for this
		if (!self.audio) {
			return;
		}

		if (!operation) {
			operation = 'save';
		}

		var value = self.album().val();

		// Changing the title from the textbox
		if (operation == 'apply') {
			self.audio.album = value;
			return;
		}

		self.renderDefaultPlaceholderAlbum = false;

		if (operation == 'save') {

			// trim the space first before do validation
			// just in case those user only type space in album field
			var noValue = ($.trim(value) === "");

			// Ensure that album name not only return you a space
			if (!noValue) {
				self.previewAlbum().html(value);
			} else {

				// Retrieve placeholder value
				value = self.album().attr("placeholder");

				// Set a flag to determine that currently render back placeholder value
				self.renderDefaultPlaceholderAlbum = true;

				// if the field doesn't have any custom content, then only replace to use placeholder value
				if (value) {
					self.previewAlbum()
						.html(value.replace(/\n/g, "<br>"));
				}
			}
		}

		// Remove the editing album class
		self.form().removeClass('editing-album');

		self.audio.album = value;

		$(document).off(self.editAlbumEvent);
	},

	checkAudioStatus: function(audioId, percentage) {
		EasySocial.ajax('site/controllers/audios/status', {
			"id": audioId,
			"uid": opts.audio.uid,
			"type": opts.audio.type,
			"createStream": 0,
			"percentage": percentage,
			"unpublished": 1
		}).done(function(permalink, percent, data, albumArt) {

			if (percent === 'done') {

				self.processing = false;

				// Set the progress bar to 100%
				self.uploaderProgressBar().css('width', '100%');
				self.uploaderProgressText().html('100%');

				// Update the state
				self.isProcessed();

				// Update the preview
				self.updatePreview('upload', data, albumArt);

				// Reset the progress bar
				self.resetProgress();

				return;
			}

			// There is a possibility that the progress is throwing errors on the line so we should skip this
			if (percent == 'ignore') {
				self.checkAudioStatus(audioId, percentage);
				return;
			}

			// Set the progress bar width
			var progress = percent + '%';
			self.uploaderProgressBar().css('width', progress);
			self.uploaderProgressText().html(progress);

			// This should run in a loop
			self.checkAudioStatus(audioId, percent);
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
		if (!self.audio) {
			return;
		}

		if (!operation) {
			operation = 'save';
		}

		// only handle for editing process
		if (opts.isEdit && $.trim(self.audio.description) == '' && self.previewDescription().html().length > 0) {
			self.audio.description = self.previewDescription().html();
			return;
		}

		var value = self.description().val();

		if (value) {
			value = value.replace(/\n/g, "<br//>");
		}

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

				self.audio.description = value;

				self.form().find(".textareaClone").remove();

				self.form().removeClass("editing-description");

				$(document).off(self.editDescriptionEvent);
				break;
			case "apply":

				// Determine currently whether got value for the description or not
				if (value === "") {
					self.renderDefaultPlaceholderDescription = true;
				}

				self.audio.description = value;
			case "revert":
				break;
		}
	},

	"{window} easysocial.story.audio.panel.insertaudiolink" : function(el, ev, url) {

		if (self.audio || self.processing || !url) {
			return;
		}

		// Switch to audio panel
		self.panelButton().click();

		// Clear up any data inside the form
		self.clearForm(true);

		// Append the audio link
		self.audioLink().val(url);

		// Process audio link
		self.insertAudio().click();
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

			// Display the audio upload form again
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

			self.audio.isEncoding = true;

			// Reset the progress bar
			self.resetProgress();

			return;
		}

		// Set status to encoding
		self.isEncoding();

		self.processing = true;

		// Update the progress since the audio needs to be converted.
		self.checkAudioStatus(response.data.id, 0);
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

	"{previewArtist} click": function() {

		var editing = self.form().hasClass('editing-artist');

		self.form().toggleClass('editing-artist', !editing);

		if (!editing) {
			self.editArtist();
		}
	},

	"{previewAlbum} click": function() {

		var editing = self.form().hasClass('editing-album');

		self.form().toggleClass('editing-album', !editing);

		if (!editing) {
			self.editAlbum();
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

	"{audioGenre} change": function(audioGenre) {
		self.currentGenre = audioGenre.val();
	},

	"{audioLink} paste": function() {
		setTimeout(function() {
			self.insertAudio().click();
		}, 100);
	},

	"{insertAudio} click": function() {

		var url = self.audioLink().val();

		if (!url || self.processing) {
			return;
		}

		// Hide the form
		self.isProcessing();

		EasySocial.ajax('ajax:/apps/user/audios/controllers/process/process', {
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
		self.renderDefaultPlaceholderArtist = false;
		self.renderDefaultPlaceholderAlbum = false;
		self.renderDefaultPlaceholderDescription = false;
	},

	//
	// Saving
	//

	"{story} save": function(element, event, save) {

		if (save.currentPanel != 'audios') {
			return;
		}

		// Here we save everything before submit
		if (opts.isEdit) {
			self.saveAlbum();
			self.saveArtist();
			self.saveTitle('apply');
			self.saveDescription();
		}

		var url = self.audioLink().val();

		// If uploading an audio link
		if (url && !self.audio) {
			save.reject(opts.errors.messages.insert);
			return;
		}

		// If sharing an audio without link and upload
		if (!url && !self.audio) {
			save.reject(opts.errors.messages.empty);
			return;
		}

		// If sharing a audio without title.
		if (!self.audio.title || $.trim(self.audio.title) == "") {
			save.reject(opts.errors.messages.title);
			return;
		}

		// Add the task for uploading audio
		self.uploadingAudio = save.addTask("uploadingAudio");

		self.save(save);
	},

	"{story} afterSubmit": function() {

		var uploadingAudio = self.uploadingAudio;

		if (!uploadingAudio) {
			return;
		}

		// Reset the form upon submission
		self.clearForm(true);

		delete self.uploadingAudio;

		if (self.audio && self.audio.isEncoding) {

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/audios/showEncodingMessage')
			});

			delete self.audio;
			return;
		}

		delete self.audio;
	},

	save: function(save) {

		var uploadingAudio = self.uploadingAudio;

		if (!uploadingAudio) {
			return;
		}

		if (self.processing) {
			save.reject(opts.errors.messages.processing);
			return;
		}

		// Attach the genre to the audio data
		self.audio.genre = self.audioGenre().val();

		if (!self.audio.genre || self.audio.genre == 0) {
			save.reject(opts.errors.messages.genre);
			return;
		}

		// Set the artist to empty if detected currently render the default placeholder artist before save
		if (self.renderDefaultPlaceholderArtist) {
			self.audio.artist = '';
		}

		// Set the album to empty if detected currently render the default placeholder album before save
		if (self.renderDefaultPlaceholderAlbum) {
			self.audio.album = '';
		}

		// Set the description to empty if detected currently render the default placeholder description before save
		if (self.renderDefaultPlaceholderDescription) {
			self.audio.description = '';
		}

		save.addData(self, self.audio);

		uploadingAudio.resolve();

		self.audioType = self.audio.type;
	},

	"{story} clear": function() {
		self.clearForm(false);

		self.renderDefaultPlaceholderArtist = false;
		self.renderDefaultPlaceholderAlbum = false;
		self.renderDefaultPlaceholderDescription = false;
	},

	"{window} easysocial.story.audio.panel.insertaudiolink" : function(el, ev, url) {

		if (self.audio || self.processing || !url) {
			return;
		}

		// Switch to audio panel
		self.panelButton().click();

		// Clear up any data inside the form
		self.clearForm(true);

		// Append the audio link
		self.audioLink().val(url);

		// Process audio link
		self.insertAudio().click();
	}
}});

// Resolve module
module.resolve();

});

});
