EasySocial.module("site/story/links", function($){

var module = this;

EasySocial.Controller("Story.Links", {
	defaultOptions: {
		urlParser: null,
		validateUrl: false,

		// Attachment item
		"{linkForm}": "[data-story-link-form]",
		"{linkInput}": "[data-story-link-input]",

		"{linkContent}": "[data-story-link-content]",
		"{linkItem}": "[data-story-link-item]",
		"{linkTitle}": "[data-story-link-title]",
		"{linkDescription}": "[data-story-link-description]",
		"{linkImages}": "[data-story-link-images]",
		"{linkImage}": "[data-story-link-image]",
		"{imageWrapper}": "[data-story-link-image-wrapper]",

		"{linkVideo}": "[data-story-link-video]",
		"{titleTextfield}": "[data-story-link-title-textfield]",
		"{descriptionTextfield}": "[data-story-link-description-textfield]",

		"{panelButton}"     : "[data-story-link-panel-button]",
		"{attachButton}"    : "[data-story-link-attach-button]",
		"{removeButton}"    : "[data-story-link-remove-button]",
		"{removeThumbnail}" : "[data-story-link-remove-image]"
	}
}, function(self, opts, base) { return {

	init: function() {

		self.linkInput().placeholder();

		if (opts.isEdit) {
			var link = {
				"title": opts.link.title,
				"description": opts.link.description,
				"url": opts.link.url,
				"image": opts.link.image
			};

			var preview = $('[data-story-link-edit]');

			link.item = $(preview).data("link", link);
			link.item.addController(EasySocial.Controller.Story.Links.Preview);

			// self.links[link.url] = link;
			self.currentLink = link;
		}
	},

	activateAttachment: function() {

		if (self.doNotFocus) {
			return;
		}

		setTimeout(function(){
			self.linkInput().focus();
			self.doNotFocus = false;
		}, 500);
	},

	//
	// Link manipulation
	//
	links: {},

	currentLink: null,

	crawling: false,

	extractUrls: function(str) {

		var urlParser = self.options.urlParser;
		var urls = str.match(urlParser);

		// Discard non http/https protocols
		if ($.isArray(urls)) {
			return $.map(urls, function(url, i){
				return $.trim(url);
			});
		} else {
			return [];
		}
	},

	fixUrl: function(url) {

		// If there's no protocol, use "http".
		var url = $.uri(url);

		if (!/http|https/.test(url.protocol())) {
			url.setProtocol("http");
		}

		return url.toString();
	},

	getLink: function(urls) {

		// If a block of string was given,
		// extract urls from it.
		if ($.isString(urls)) {
			urls = self.extractUrls(urls);
		}

		if (urls.length < 1) {
			var dfd = $.Deferred();
			dfd.reject(opts.errors.messages.tlds);

			return dfd;
		}

		// Get only the first url
		var url = urls[0];

		// If this is a new url,
		// create a new link object for it.
		link = self.links[url] || self.createLink(url);

		// When the link is resolved,
		// add link to the attachment item.
		return link;
	},

	createLink: function(url) {

		// Create a new link object
		var link = self.links[url] = $.Deferred();

		// Add url property
		link.url = url;

		self.crawling = true;

		// Get link info from crawler
		EasySocial.ajax('site/controllers/crawler/fetch', {
			"url": url,
			"preview": 1
		}).done(function(data, preview) {

			if (!data) {
				link.reject();
			}

			// Create link item
			link.item = $(preview).data("link", data);

			link.item.addController(EasySocial.Controller.Story.Links.Preview);

			link.resolve(link);
		})
		.fail(function(msg){
			link.reject(msg);
		})
		.always(function() {
			self.crawling = false;
		});

		return link;
	},

	addLink: function(link) {

		// Add link item to attachment item
		self.linkContent()
			.empty()
			.append(link.item);

		self.linkForm()
			.hide();

		self.currentLink = link;
	},

	removeLink: function() {

		self.linkItem()
			.detach();

		self.linkForm()
			.show();

		self.currentLink = null;
	},

	checkAllowedPanel: function() {

		var allowedPanel = ["links", "text"];
		var allow = false;

		$.each(allowedPanel, function(index, panelName){
			var pluginSelector = '[data-story-plugin-name="' + panelName + '"]';
			var panel = $(pluginSelector);

			if (panel.hasClass('active')) {
				allow = true;
				return false;
			}
		});

		return allow;
	},

	checkVideoLink: function(url) {

		var pluginContent = self.story.panelContent('[data-story-plugin-name="videos"]');
		var allowLink = $(pluginContent).find('[data-video-form]').data('allow-link');

		if (!allowLink) {
			return false;
		}

		// General video url
		var videoURL = url.match(/(youtube|youtu|dailymotion|twitch|liveleak)\.(com|be|tv)\/((watch\?v=([-\w]+))|(view\?i=([-\w]+))|((video|videos)\/([-\w]+)))/);

		// Vimeo url checking eg. https://vimeo.com/123456
		var vimeoURL = url.match(/(vimeo)\.(com)\/(([-\w]+))/);
		var twitchURL = url.match(/twitch.tv/);

		if (videoURL !== null || vimeoURL !== null || twitchURL !== null) {
			return true;
		}

		return false;
	},

	checkAudioLink: function(url) {

		var pluginContent = self.story.panelContent('[data-story-plugin-name="audios"]');
		var allowLink = $(pluginContent).find('[data-audio-form]').data('allow-link');

		if (!allowLink) {
			return false;
		}

		// General audio url
		var spotifyURL = url.match(/^(spotify:|https:\/\/(open|play)+\.spotify\.com\/)/);
		var soundcloudURL = url.match(/^https?:\/\/(soundcloud\.com|snd\.sc)\/(.*)$/);

		if (spotifyURL !== null || soundcloudURL !== null) {
			return true;
		}

		return false;
	},

	//
	// Link form
	//
	"{attachButton} click": function() {

		var linkInput = self.linkInput();
		var linkForm  = self.linkForm();
		var url = $.trim(self.linkInput().val());

		// If there's no url, stop.
		if (url === "") {
			return;
		}

		// Fix the url
		url = self.fixUrl(url);

		// Set fixed link back to input box
		self.linkInput().val(url);

		// Set link form as busy
		linkForm.addClass("busy");

		// Get link
		self.getLink(url)
			.done(function(link){
				self.addLink(link);
			})
			.fail(function(error){
				self.story.setMessage(error, "error");
			})
			.always(function(){
				linkForm.removeClass("busy");
			});
	},

	"{removeButton} click": function(button) {

		self.currentLink.disabled = true;

		self.removeLink();
	},


	"{linkInput} keyup": function(input, event) {

		if (event.keyCode == 13) {
			self.attachButton().click();
		}
	},

	"{linkInput} paste": function(input, event) {
		setTimeout(function() {
			self.attachButton().click();
		}, 100);
	},

	"{story.textField} input": $._.debounce(function(textField, event) {

		// Don't look for links if we've already added one
		if (self.currentLink || self.crawling) {
			return;
		}

		// Retrieve the last typed url
		var content = textField.val();
		var urls = self.extractUrls(content);
		var url = urls[urls.length - 1];

		if (!url) {
			return;
		}

		videoLink = self.checkVideoLink(url);

		// We let video panel to handle video link
		if (videoLink) {
			$(window).trigger('easysocial.story.video.panel.insertvideolink', [url]);
			return;
		}

		audioLink = self.checkAudioLink(url);

		// We let audio panel to handle audio link
		if (audioLink) {
			$(window).trigger('easysocial.story.audio.panel.insertaudiolink', [url]);
			return;
		}

		// Check for allowed panel for the link to active
		if (!self.checkAllowedPanel()) {
			return;
		}

		// Check if link has been crawled before
		var url = self.fixUrl(url);
		var link = self.links[url];

		if (link && link.disabled) {
			return;
		}

		if (self.options.validateUrl) {
			var failed = false;

			EasySocial.ajax('site/controllers/crawler/validate', {
				"url": url
			}).done(function() {

				// Set the url as the value
				self.linkInput().val(url);

				// Do not focus when attachment is activated
				self.doNotFocus = true;

				// Trigger links attachment
				self.panelButton().click();

				// Add link
				self.attachButton().click();
			});

			return;
		}

		// Set the url as the value
		self.linkInput().val(url);

		// Do not focus when attachment is activated
		self.doNotFocus = true;

		// Trigger links attachment
		self.panelButton().click();

		// Add link
		self.attachButton().click();

	}, 950),

	//
	// Saving
	//
	"{story} save": function(element, event, save) {

		if (save.currentPanel != 'links') {
			return;
		}

		if (!self.currentLink) {
			save.reject(opts.errors.messages.insert);
			return;
		}

		var data = {
					title: self.titleTextfield().val(),
					description: self.descriptionTextfield().val(),
					url: self.currentLink.url,
					video: self.linkVideo().val()
				};

		if (!self.removeThumbnail().is(":checked")) {

			data.image = self.imageWrapper('.active')
							.find('img')
							.attr('src');
		}

		save.addData(self, data);
	},

	"{story} clear": function() {

		self.linkInput().val("");

		self.removeLink();
	}
}});

EasySocial.Controller('Story.Links.Preview', {
	defaultOptions: {

		"{previousImage}": "[data-story-link-image-prev]",
		"{nextImage}": "[data-story-link-image-next]",
		"{image}": "[data-story-link-image]",
		"{imageWrapper}": "[data-story-link-image-wrapper]",
		"{imagesWrapper}"   : "[data-story-link-images]",
		"{imageIndex}"      : "[data-story-link-image-index]",
		"{removeThumbnail}" : "[data-story-link-remove-image]",
		"{imageDimensions}": "[data-story-link-image-dimensions]",
		"{imageWidth}": "[data-image-width]",
		"{imageHeight}": "[data-image-height]",

		"{title}": "[data-story-link-title]",
		"{description}": "[data-story-link-description]",
		"{titleTextfield}"      : "[data-story-link-title-textfield]",
		"{descriptionTextfield}": "[data-story-link-description-textfield]"
	}
}, function(self, opts, base) {

	return {

	init: function() {

		// Init dimensions
		self.initDimensions();
	},

	initDimensions: function() {

		// When the images are loaded, set the width and height accordingly.
		self.image()
			.on('load', function() {
				var width = this.naturalWidth;
				var height = this.naturalHeight;

				var wrapper = $(this).parent();

				// Set the width and height on the width and height
				wrapper.find(self.imageWidth.selector)
					.html(width);

				wrapper.find(self.imageHeight.selector)
					.html(height);
			});
	},

	"{removeThumbnail} click" : function() {
		var isChecked = self.removeThumbnail().is(':checked');

		if (isChecked) {
			self.imagesWrapper().hide();
		} else {
			self.imagesWrapper().show();
		}

		self.element.toggleClass("has-images", !isChecked);
	},

	"{previousImage} click" : function() {
		var currentImage = self.imageWrapper('.active');
		var prevImage = currentImage.prev();
		var index = parseInt(self.imageIndex().html());
		var nextIndex = index - 1;

		if (prevImage.length > 0) {
			currentImage.removeClass('active');
			prevImage.addClass('active');

			self.imageIndex().html(nextIndex);
		}
	},

	"{nextImage} click" : function() {
		var currentImage = self.imageWrapper('.active');
		var nextImage = currentImage.next();

		var index = parseInt(self.imageIndex().html());
		var nextIndex = index + 1;

		if (nextImage.length > 0) {
			currentImage.removeClass('active');
			nextImage.addClass('active');

			self.imageIndex().html(nextIndex);
		}
	},

	"{title} click": function() {

		var editingTitle = self.element.hasClass("editing-title");

		self.element.toggleClass("editing-title", !editingTitle);

		if (!editingTitle) {
			self.editTitle();
		}
	},

	editTitleEvent: "click.es.story.editLinkTitle",

	editTitle: function() {

		self.element.addClass("editing-title");

		setTimeout(function(){

			self.titleTextfield()
				.val(self.title().text())
				.focus()[0].select();

			$(document).on(self.editTitleEvent, function(event) {
				if (event.target!==self.titleTextfield()[0]) {
					self.saveTitle("save");
				}
			});
		}, 1);
	},

	saveTitle: function(operation) {

		if (!operation) {
			operation = save;
		}

		var value = self.titleTextfield().val();

		if (operation == 'save') {
			if (value === '') {
				value = self.title().data('default');
			}

			self.title().html(value);
		}

		self.element.removeClass("editing-title");

		$(document).off(self.editTitleEvent);
	},

	"{titleTextfield} keyup": function(el, event) {

		// Escape
		if (event.keyCode==27) {
			self.saveTitle("revert");
		}
	},

	"{description} click": function() {

		var editingDescription = self.element.hasClass("editing-description");

		self.element.toggleClass("editing-description", !editingDescription);

		if (!editingDescription) {
			self.editDescription();
		}
	},

	editDescriptionEvent: "click.es.story.editLinkDescription",

	editDescription: function() {

		self.element.addClass("editing-description");

		setTimeout(function(){

			var descriptionClone = self.description().clone(),
				noDescription = descriptionClone.hasClass("no-description");

			descriptionClone.wrapInner(self.descriptionTextfield());

			if (noDescription) {
				self.descriptionTextfield().val("");
			}

			// self.descriptionTextfield()
			//  .expandingTextarea();

			self.descriptionTextfield()
				.focus()[0].select();

			$(document).on(self.editDescriptionEvent, function(event) {

				if (event.target!==self.descriptionTextfield()[0]) {
					self.saveDescription("save");
				}
			});
		}, 1);
	},

	saveDescription: function(operation) {
		if (!operation) operation = save;

		var value = self.descriptionTextfield().val().replace(/\n/g, "<br//>");

		switch (operation) {

			case "save":

				var noValue = (value==="");

				self.description()
					.toggleClass("no-description", noValue);

				if (noValue) {
					value = self.descriptionTextfield().attr("placeholder");
				}

				self.description()
					.html(value);
				break;

			case "revert":
				break;
		}

		self.element.find(".textareaClone").remove();

		self.element.removeClass("editing-description");

		$(document).off(self.editDescriptionEvent);
	},

	"{descriptionTextfield} keyup": function(el, event) {
		// Escape
		if (event.keyCode==27) {
			self.saveDescription("revert");
		}
	}
}});

module.resolve();

});
