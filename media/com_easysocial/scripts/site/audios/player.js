EasySocial.module('site/audios/player', function($) {

var module = this;

EasySocial.require()
.library('wavesurfer')
.done(function($) {

EasySocial.Controller('Audios.Player', {
	defaultOptions: {
		"{playButton}": "[data-play-button]",
		"{pauseButton}": "[data-pause-button]",
		"{audioContainer}": "[data-audio-container]",
		"{duration}": "[data-playing-duration]"

	}
}, function(self, opts, base) { return {

	id: "",
	container: "",
	file: "",
	player: "",
	loaded: false,

	init: function() {
		self.container = base.find('[data-audio-wave]').attr('id');
		self.file = base.data('file');

		var ctx = document.createElement('canvas').getContext('2d');
		var linGrad = ctx.createLinearGradient(0, 64, 0, 200);
		linGrad.addColorStop(0.5, 'rgba(255, 255, 255, 1.000)');
		linGrad.addColorStop(0.5, 'rgba(183, 183, 183, 1.000)');

		self.player = WaveSurfer.create({
			container: '#' + self.container,
			interact: true,
			waveColor: linGrad,
			progressColor: 'hsla(200, 100%, 30%, 0.5)',
			cursorColor: '#fff',
			barWidth: 2,
			backend: 'MediaElement'
		});

		var playButtons = document.querySelectorAll('[data-play-button-mini], [data-play-button]');
		var playButtons = Array.prototype.slice.call(playButtons,0);

		// If user click on any other play button, we trigger pause for others player
		playButtons.forEach(function(playButton){
			playButton.addEventListener('click', function (e) {
				self.pauseButton().click();
			});
		});

		var filters = document.querySelectorAll('[data-audios-filter]');
		filters = Array.prototype.slice.call(filters,0);

		// If user click on any other filter on the sidebar, we destroy the player
		filters.forEach(function(filter){
			filter.addEventListener('click', function (e) {
				self.player.destroy();
			});
		});

		self.player.on('audioprocess', function () {
			self.duration().html(self.formatTime(self.player.getCurrentTime()));
		});
	},

	formatTime: function(time) {
		return [
			Math.floor((time % 3600) / 60), // minutes
			('00' + Math.floor(time % 60)).slice(-2) // seconds
		].join(':');
	},

	"{pauseButton} click": function(el) {

		self.player.pause();

		self.togglePlayPause(false);

		// el.addClass('t-hidden');
		// self.playButton().removeClass('t-hidden');
		// self.audioContainer().removeClass('is-playing');
	},

	"{playButton} click": function(el) {
		// Add loading state
		el.addClass('is-loading');

		if (self.loaded == false) {
			this.loadPlayer(el);

			// update hit for audio
			EasySocial.ajax('site/controllers/audios/hit',{
				"id": base.data('id')
			});
		} else {
			el.removeClass('is-loading');
			self.player.play();

			self.togglePlayPause(true);
			self.pauseButton().css('opacity', '0.5');
		}

		self.audioContainer().addClass('is-playing');
	},

	togglePlayPause: function(isPlaying) {

		self.playButton().toggleClass('t-hidden', isPlaying);
		self.audioContainer().toggleClass('is-playing', isPlaying);
		self.pauseButton().toggleClass('t-hidden', !isPlaying);
	},

	"{audioContainer} mouseover": function(el, ev) {
		if (self.audioContainer().hasClass('is-playing')) {
			this.changeOpacity(ev);
		}
	},

	"{audioContainer} mouseout": function(el, ev) {
		if (self.audioContainer().hasClass('is-playing')) {
			this.changeOpacity(ev);
		}
	},

	changeOpacity: function(ev) {
		var value = ev.type == 'mouseout' ? '0.5' : '';
		self.pauseButton().css('opacity', value);
	},

	loadPlayer: function(button) {
		self.player.load(self.file);

		self.player.on('ready', function () {
			button.removeClass('is-loading');
			button.addClass('t-hidden');
			self.pauseButton().removeClass('t-hidden');
			self.pauseButton().css('opacity', '0.5');

			self.player.playPause();

			self.loaded = true;
		});
	}

}});

module.resolve();

});
});
