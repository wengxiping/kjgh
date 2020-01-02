EasySocial.module('site/audios/playlist', function($) {

var module = this;

EasySocial.require()
.script('site/audios/suggest')
.library('wavesurfer', 'ui/slider')
.done(function($) {

EasySocial.Controller('Audios.Playlist', {
	defaultOptions: {
		nowPlaying: "",
		playlistId: "",
		"{wrapper}": "[data-audio-playlist]",
		"{playlistPlayer}": "[data-audio-playlist-player]",
		"{playingInfo}": "[data-playing-info]",
		"{playingTitle}": "[data-playing-title]",
		"{playingDuration}": "[data-playing-duration]",
		"{playingAlbumart}": "[data-playing-albumart]",

		// Buttons
		"{playpauseButton}": "[data-playpause-button]",
		"{nextButton}": "[data-next-button]",
		"{prevButton}": "[data-prev-button]",
		"{volumeButton}": "[data-volume-button]",
		"{removeTrack}": "[data-remove-track]",
		"{volumeSlider}": "[data-volume-slider]",

		"{playlist}": "[data-playlist]",
		"{track}": "[data-playlist-track]",

		// Manage Playlist
		"{listActions}": "[data-list-actions]",
		"{deletePlaylist}": "[data-list-actions] [data-delete]",
		"{addToPlaylist}": "[data-add]"
	}
}, function(self, opts, base) { return {

	currentTrack: 0,
	id: "",
	tracks: "",
	player: "",
	initialized: false,
	currentVolume: 0.2,
	init: function() {

		self.id = base.data('id');
		self.tracks = document.querySelectorAll('[data-playlist] [data-playlist-track]');

		// If there is no track, just return.
		if (self.tracks.length < 1) {
			return;
		}

		// Initialize player
		self.initPlayer();

		// Initialize volume slider
		self.initSlider();

		tracks = Array.prototype.slice.call(self.tracks,0);

		// Load the track on click
		tracks.forEach(function(track, index){
			track.addEventListener('click', function (e) {

				// We don't want to trigger this when click on the trash
				if ($(e.target).hasClass('fa-trash')) {
					return;
				}

				self.setCurrentSong(index);
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

		// Play on audio load only if the player is on playing state
		self.player.on('ready', function () {
			self.playlistPlayer().removeClass('is-loading');
			if (self.playpauseButton().hasClass('is-playing')) {
				self.player.play();
			}
		});

		// Go to the next track on finish
		self.player.on('finish', function () {
			self.setCurrentSong((self.currentTrack + 1) % self.tracks.length);
		});

		self.player.on('audioprocess', function () {
			self.playingDuration().html(self.formatTime(self.player.getCurrentTime()));
		});

		// Load the first track
		self.setCurrentSong(self.currentTrack);
	},

	formatTime: function(time) {
		return [
			Math.floor((time % 3600) / 60), // minutes
			('00' + Math.floor(time % 60)).slice(-2) // seconds
		].join(':');
	},

	initPlayer: function() {
		var ctx = document.createElement('canvas').getContext('2d');
		var linGrad = ctx.createLinearGradient(0, 64, 0, 200);

		linGrad.addColorStop(0.5, '#ffffff');
		linGrad.addColorStop(0.5, '#b7b7b7');

		// Initiallize the player
		self.player = WaveSurfer.create({
			container: '#es-waveform-playlist',
			interact: true,
			waveColor: '#C7C6D5',
			progressColor: '#9595B1',
			progressColor: '#9595B1',
			cursorColor: '#fff',
			barWidth: 2,
			cursorWidth: 0,
			height: 80
		});

		self.initialized = true;
	},

	initSlider: function() {
		self.volumeSlider().slider({
			range: "max",
			min: 0,
			max: 20,
			value: 4,
			slide: function(event, ui) {
				var volume = ui.value / 20;

				// Change the player volume when it slide
				self.player.setVolume(volume);
				self.currentVolume = volume;
		}
		});

		// self.player.setVolume(self.volumeSlider().slider("value"));
	},

	initTracksListener: function() {
		self.tracks = document.querySelectorAll('[data-playlist] [data-playlist-track]');
		tracks = Array.prototype.slice.call(self.tracks,0);

		// Load the track on click
		tracks.forEach(function(track, index){
			track.addEventListener('click', function (e) {

				// We don't want to trigger this when click on the trash
				if ($(e.target).hasClass('fa-trash')) {
					return;
				}

				self.setCurrentSong(index);
			});
		});
	},

	setCurrentSong: function(index) {

		// Remove 'is-active' class from all the item
		self.tracks[self.currentTrack].classList.remove('is-active');

		self.currentTrack = index;

		self.tracks[self.currentTrack].classList.add('is-active');

		// Update the Now Playing info
		self.updateNowPlaying(self.tracks[self.currentTrack]);

		self.playlistPlayer().addClass('is-loading');

		var file = $(self.tracks[self.currentTrack]).data('file');
		var audioId = $(self.tracks[self.currentTrack]).data('audio-id');

		self.player.load(file);

		self.player.setVolume(self.currentVolume);

		self.updateHit(audioId);
	},

	updateHit:function(audioId) {
		// update hit for audio
		EasySocial.ajax('site/controllers/audios/hit',{
			"id": audioId
		});
	},

	updateNowPlaying: function(currentPlaying){
		var title = $(currentPlaying).find('[data-title]').html();
		var duration = $(currentPlaying).find('[data-duration]').html();
		var albumArt = $(currentPlaying).data('albumart');

		self.playingTitle().html(title);
		self.playingDuration().html(duration);
		self.playingAlbumart().css('background-image', 'url('+ albumArt +')');
	},

	"{playpauseButton} click": function(el) {
		self.player.playPause();
		el.toggleClass('is-playing');
	},

	"{nextButton} click": function (el) {
		self.setCurrentSong((self.currentTrack + 1) % self.tracks.length);
	},

	"{prevButton} click": function (el) {
		var prevIndex = self.currentTrack - 1;
		self.setCurrentSong((prevIndex < 0 ? 0 : prevIndex) % self.tracks.length);
	},

	"{volumeButton} click": function (el) {
		el.toggleClass('is-muted');
		self.player.setMute(el.hasClass('is-muted'));
	},

	"{removeTrack} click": function (el) {
		var listMapId = el.parents(self.track.selector).data('listmap-id');

		EasySocial.ajax('site/controllers/audios/removeFromPlaylist', {
			"playlistId": opts.playlistId,
			"listMapId": listMapId
		}).done(function() {

			// Remove the item from the list.
			self.removeItem(listMapId, 'list');
		});
	},

	"{deletePlaylist} click" : function(track) {
		var actions = track.parents(self.listActions.selector);
		var id = actions.data('id');

		EasySocial.dialog({
			content: EasySocial.ajax("site/views/audios/confirmDeletePlaylist", {"id": id}),
			bindings: {
				"{deleteButton} click" : function() {
					$('[data-playlist-delete-form]').submit();
				}
			}
		});
	},

	"{addToPlaylist} click": function(track) {

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/audios/assignList', {"id" : self.id}),
			bindings: {

				"{insertButton} click" : function() {
					var items = this.suggest().textboxlist("controller").getAddedItems();

					EasySocial.ajax('site/controllers/audios/assignItem', {
						"uid": $.pluck(items, "id"),
						"listId": self.id
					}).done(function(contents) {

						// Hide any notice messages.
						$('[data-assignAudios-notice]').hide();

						$(contents).each(function(i, item) {

							// Pass the item to the parent so it gets inserted into the playlist.
							self.insertItem(item);

							// Assign back the event listener to the new list of tracks
							self.initTracksListener();

							// If the playlist is previously not initialized, we should initialize it.
							if (self.initialized === false) {
								self.init();
								self.playingInfo().html(opts.nowPlaying);
							}

							// Close the dialog
							EasySocial.dialog().close();
						});

					}).fail(function(message) {
						$('[data-assignAudios-notice]').addClass('alert alert-error')
							.html(message.message);
					});
				}
			}
		});
	},

	insertItem: function(item) {

		// Hide any empty notices.
		self.playlist().removeClass('is-empty');

		// Prepend the result back to the list
		$(item).appendTo(self.playlist());

		// Update the counter for the list items.
		self.trigger('updateListCounters');
	},

	removeItem: function(id, source) {
		// Remove item from the list.
		var item = self.track('[data-listmap-id="' + id + '"]');

		item.remove();

		// Update the counter for the list items.
		self.trigger('updateListCounters');
	}

}});

module.resolve();

});
});
