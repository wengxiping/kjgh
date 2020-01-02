EasySocial.module('site/videos/preview', function($) {

var module = this;

EasySocial.Controller('Videos.Preview', {
	defaultOptions: {
		id : null,

		"{playerWrapper}": "[data-embed-player-wrapper]",
		"{playButton}": "[data-embed-play-button]",

		"{videoPreview}": "[data-es-embed-preview]",
		"{videoPreviewResult}": "[data-es-embed-preview-result]"
	}
}, function(self, opts, base) { return {

	init: function() {
		opts.id = self.element.data('id');
	},

	insertEmbed: function() {
		self.playButton().addClass('t-hidden');
		self.playerWrapper().addClass('vjs-waiting');

		// Retrieve the embed codes
		EasySocial.ajax('site/views/videos/getEmbedCodes', {
			'id' : opts.id
		}).done(function(output) {
			self.videoPreviewResult().html(output);

			// Check for iframe load
			var iframe = self.videoPreviewResult().find('iframe');

			if (iframe.length > 0) {
				iframe.on('load', function() {
					self.videoPreviewResult().removeClass('t-hidden');
					self.videoPreview().addClass('t-hidden');

					// Once play button pressed, we adjust the video contaner ratio accodingly
					container = self.element.parents('[data-video-container]');
					ratio = container.data('video-ratio');
					container.addClass(ratio);
				});
			} else {
				// Display error message?
			}
		});
	},

	click: false,

	"{playerWrapper} click": function() {

		// Prevent double click
		if (self.click) {
			return;
		}

		self.click = true;
		self.insertEmbed();
	}
}});

module.resolve();

});
