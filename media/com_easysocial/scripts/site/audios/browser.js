EasySocial.module('site/audios/browser', function($) {

var module = this;

EasySocial.Controller('Audios.Browser', {
	defaultOptions: {
		"{sorting}": "input[name='sorting']",
		"{sortItem}": "[data-sorting]",
		"{counters}": "[data-counter]",

		// content wrapper
		"{wrapper}": "[data-wrapper]",

		// Audios result
		"{result}": "[data-audios-result]",
		"{list}": "[data-result-list]",

		// Audio actions
		"{item}": "[data-audio-item]",
		"{deleteButton}": "[data-audio-delete]",
		"{featureButton}": "[data-audio-feature]",
		"{unfeatureButton}": "[data-audio-unfeature]",
		"{playlistItem}": "[data-playlist-item]"
	}
}, function(self, opts, base) { return {

	clicked: false,
	currentFilter: "",
	currentSorting: "",
	genreId: null,
	isSort: false,

	getPlaylist: function(playlistId, callback) {

		self.wrapper().addClass('is-loading');
		self.result().empty();

		EasySocial.ajax('site/views/audios/loadPlaylist', {
			id: playlistId
		}).done(function(output) {

			if (typeof(callback) == 'function') {
				callback.apply(output);
			}

			self.activeFilter.parent().removeClass('is-loading');

			self.result().html(output);
			self.wrapper().removeClass('is-loading');

			$('body').trigger('afterUpdatingContents', [output]);
		});
	},

	getAudios: function(callback) {

		if (!self.currentSorting) {
			// Set the current sorting
			self.currentSorting = self.sorting().val();
		}

		if (!self.currentFilter) {
			// Set the current sorting
			self.currentFilter = self.activeFilter.data('type');
		}

		// if still empty the filter, just set to all.
		if (!self.currentFilter) {
			self.currentFilter = "all";
		}

		var isSortReq = self.isSort ? "1" : "0";

		EasySocial.ajax('site/controllers/audios/getAudios',{
			"filter": self.currentFilter,
			"genreId": self.genreId,
			"sort": self.currentSorting,
			"uid": opts.uid,
			"type": opts.type,
			"hashtags": opts.hashtag,
			"hashtagFilterId": self.hashtagId,
			"isSort": isSortReq
		}).done(function(output) {

			if (typeof(callback) == 'function') {
				callback.apply(output);
			}

			if (self.isSort) {
				self.result().removeClass('is-loading');
				self.list().html(output);
			} else {
				self.wrapper().removeClass('is-loading');
				self.result().html(output);
			}

			$('body').trigger('afterUpdatingContents', [output]);
		});
	},

	"{sortItem} click" : function(sortItem, event) {

		// Get the sort type
		var type = sortItem.data('type');
		self.currentSorting = type;

		if (!self.hashtagId) {
			self.hashtagId = sortItem.data('tag-id');
		}

		var sortFilter = sortItem.data('filter');

		if (sortFilter == 'genre') {
			self.genreId = sortItem.data('id');
		}

		self.isSort = true;

		// Route the item so that we can update the url
		sortItem.route();

		self.result().addClass('is-loading');
		self.list().empty();

		self.getAudios();
	},

	"{deleteButton} click": function(deleteButton, event) {

		var item = deleteButton.parents(self.item.selector);
		var id = item.data('id');
		var returnUrl = deleteButton.data('return');

		var options = {
			"id": id
		};

		if (returnUrl) {
			options["return"] = returnUrl;
		}

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/audios/confirmDelete', options)
		});
	},

	"{unfeatureButton} click": function(unfeatureButton, event) {
		var item = unfeatureButton.parents(self.item.selector);
		var id = item.data('id');
		var returnUrl = unfeatureButton.data('return');

		var options = {
			"id": id
		};

		if (returnUrl.length > 0) {
			options["return"] = returnUrl;
		}

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/audios/confirmUnfeature', options)
		});
	},

	"{featureButton} click": function(featureButton, event) {
		var item = featureButton.parents(self.item.selector);
		var id = item.data('id');
		var returnUrl = featureButton.data('return');

		var options = {
			"id": id
		};

		if (returnUrl) {
			options["return"] = returnUrl;
		}

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/audios/confirmFeature', options)
		});
	},

	"{playlistItem} click": function(playlistItem, event) {
		var item = playlistItem.parents(self.item.selector);
		var audioId = item.data('id');
		var playlistId = playlistItem.data('id');
		var previouslyAdded = playlistItem.find('i').length > 0;
		var overlayNotice = item.find('[data-overlay-notice]');

		EasySocial.ajax('site/controllers/audios/addToPlaylist',{
			"playlistId": playlistId,
			"audioId": audioId
		}).done(function(message) {
			self.trigger('updateListCounters');

			if (previouslyAdded === false) {
				playlistItem.append('<i class="fa fa-check pull-right"></i>');
			}

			overlayNotice.fadeIn('fast');

			setTimeout(function(){
				overlayNotice.fadeOut('slow');
			}, 2000);
		});
	}
}});

module.resolve();


});
