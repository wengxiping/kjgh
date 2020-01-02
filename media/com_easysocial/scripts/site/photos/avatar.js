EasySocial.module("site/photos/avatar", function($){

var module = this;

EasySocial.require()
.library("imgareaselect")
.done(function(){

EasySocial.Controller("Photos.Avatar", {
	defaultOptions: {
		"uid": null,
		"type": null,
		"redirect": true,
		"redirectUrl" : "",
		"{image}": "[data-photo-image]",
		"{viewport}": "[data-photo-avatar-viewport]",
		"{photoId}": "[data-photo-id]",
		"{userId}": "[data-user-id]",
		"{createButton}": "[data-create-button]",
		"{selection}": "[data-selection-box]",
		"{loadingIndicator}": "[data-photos-avatar-loading]"
	}
}, function(self, opts) { return {

	init: function() {
		self.setLayout();
	},

	data: function() {

		var viewport = self.viewport();
		var width  = viewport.width();
		var height = viewport.height();

		var selection = viewport.imgAreaSelect({"instance": true}).getSelection();
		var data = {
					"id": self.photoId().val(),
					"uid": self.options.uid,
					"type": self.options.type,
					"top": selection.y1 / height,
					"left": selection.x1 / width,
					"width": selection.width / width,
					"height": selection.height / height
				};

		return data;
	},

	imageLoaders: {},

	setLayout: function() {

		var imageHolder = self.image();

		// Using this instead of the other one above for urls that may have /*/ in it.
	    // imageUrl = $.uri(imageHolder.css("backgroundImage")).extract(0),
		var imageUrl = imageHolder.css("backgroundImage").replace(/^url\(['"]?/,'').replace(/['"]?\)$/,'');
		var imageLoaders = self.imageLoaders;
		var imageLoader = imageLoaders[imageUrl] || (self.imageLoaders[imageUrl] = $.Image.get(imageUrl));

		imageLoader
			.done(function(imageEl, image) {

				var size = $.Image.resizeWithin(image.width, image.height, imageHolder.width(), imageHolder.height());

				var min = Math.min(size.width, size.height);
				var x1  = Math.floor((size.width  - min) / 2);
				var y1  = Math.floor((size.height - min) / 2);
				var x2  = x1 + min;
				var y2  = y1 + min;

				setTimeout(function() {
					// Enable the create avatar button
					self.createButton().enabled(true);

					self.viewport()
						.css(size)
						.imgAreaSelect({
							handles: true,
							aspectRatio: "1:1",
							parent: self.image(),
							x1: x1,
							y1: y1,
							x2: x2,
							y2: y2,
							onSelectEnd: function(viewport, selection) {
								var hasSelection = !(selection.width=="0" && selection.height=="0");

								// This ensures that the user selects an area at least
								self.createButton().enabled(hasSelection);
							}
						});
					}, 450);
		    });
	},

	"{createButton} click": function(createButton, event) {
		var data = self.data();

		// Disabled the button
		createButton.attr('disabled', 'disabled');
		createButton.find('[data-create-button-loader]').addClass('is-active');
		var task = EasySocial.ajax("site/controllers/photos/createAvatar", data)
						.done(function(photo, user) {
							if (opts.redirect) {
								var url = self.options.redirectUrl;
								url = url.replace('&amp;', '&');

								window.location = url;
							}
							
						}).fail(function(message, type) {
							self.setMessage(message, type);
						});

		self.trigger("avatarCreate", [task, data, self]);
	}

}});

module.resolve();

});

});
