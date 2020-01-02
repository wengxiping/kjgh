EasySocial.module('site/audios/player.mini', function($) {

var module = this;

EasySocial.require()
.library('wavesurfer')
.done(function($) {

EasySocial.Controller('Audios.PlayerMini', {
	defaultOptions: {
		"{playButton}": "[data-play-button-mini]",
		"{pauseButton}": "[data-pause-button-mini]",
		"{audioContainer}": "[data-audio-container]",
		"{duration}": "[data-playing-duration]",
		"{bar}": "[data-player-bar]",
		"{barProgress}": "[data-bar-progress]",
		"{marqueeTitle}": "[data-marquee-title]"
	}
}, function(self, opts, base) { return {

	id: "",
	container: "",
	file: "",
	player: "",
	loaded: false,
	interval: false,

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

		self.player.on('audioprocess', function (result) {
			var duration = self.player.getDuration();
			var current = result;

			var percent = Math.floor((current / duration) * 100) + '%';
			self.barProgress().css('width', percent);

			if (percent == '100%') {
				self.player.stop();
				self.togglePlayPause(false);
			}
		});

		self.bar().click(function(e) {
			var bar = $(this);

			// to get part of width of progress bar clicked
			var widthclicked = e.pageX - bar.offset().left;
			var totalWidth = bar.width();

			// do calculation of the seconds clicked
			var calc = (widthclicked / totalWidth );
			self.player.seekTo(calc);
		});

		self.player.on('seek', function (result) {
			var duration = self.player.getDuration();
			var current = result;

			var percent = Math.floor((current / duration) * 100) + '%';
			self.barProgress().css('width',percent);
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

		clearInterval(self.interval);
		self.marqueeTitle().css('text-indent', 0);
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

		var mar = self.marqueeTitle();

		if (mar.get(0).scrollWidth > mar.outerWidth()) {

			var indent = mar.width();

			mar.marquee = function() {
				indent--;
				mar.css('text-indent', indent);
				if (indent < -1 * mar.width()) {
					indent = mar.width();
				}
			};

			self.interval = setInterval(mar.marquee, 1000/60);
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
