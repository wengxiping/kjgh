EasySocial.module('site/audios/process', function($) {

var module = this;

EasySocial.Controller('Audios.Process', {
	defaultOptions: {
		"{progressBar}": "[data-audio-progress-bar]",
		"{progressResult}": "[data-audio-progress-result]"
	}
}, function(self, opts, base) { return {

	init: function() {

		// Set the global options
		opts.id = base.data('id');

		self.processAudio();
	},

	processAudio: function() {

		// Initialize the audio processing here
		EasySocial.ajax('site/controllers/audios/process', {
			"id": opts.id
		}).done(function() {

			// Run check status
			self.status(opts.id);
		});
	},

	status: function(audioId) {
		// Initialize the audio processing here
		EasySocial.ajax('site/controllers/audios/status', {
			"id": audioId
		}).done(function(permalink, progress) {

			if (progress == 'done') {
				self.progressBar().css('width', '100%');
				self.progressResult().html('100%');

				// Redirect the user upon completion
				window.location = permalink;

				return;
			}

			// There is a possibility that the progress is throwing errors on the line so we should skip this
			if (progress == 'ignore') {
				self.status(audioId);
				return;
			}

			var percentage = progress + '%';

			// Reiterate the same method again until it's completed.
			self.progressBar().css('width', percentage);
			self.progressResult().html(percentage);

			self.status(audioId);

			return;
		});
	}

}});

module.resolve();
});
