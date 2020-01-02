EasySocial.module('site/audios/filter', function($) {

var module = this;

EasySocial.require()
.script('site/audios/browser')
.done(function($) {

EasySocial.Controller('Audios.Filter', {
	defaultOptions: {
		"{filter}": "[data-es-audio-filters] [data-filter-item]",
		"{createFilter}": "[data-audio-create-filter]",
		"{browserController}": "[data-audios-listing]"
	}
}, function(self, opts, base) { return {

	controller: null,

	getController: function() {
		var controller = self.browserController().controller();

		return controller;
	},

	init: function() {
		self.controller = self.getController();

		if (self.controller) {
			self.controller.activeFilter = self.filter('[data-type=' + opts.active + ']');
		}
	},

	setActiveFilter: function(filter) {
		self.filter().removeClass('active');

		filter.addClass('active');

		// Update the URL on the browser
		filter.find('a').route();

		// Set loading on the correct filter
		filter.addClass('is-loading');
	},

	"{filter} click": function(filter, event) {

		// Since controller doesn't exist, we should just redirect to the view
		if (!self.controller) {
			return;
		}

		// Prevent bubbling up
		event.preventDefault();
		event.stopPropagation();

		var type = filter.data('type');

		// Route the inner filter links
		if (filter.is('a')) {
			filter.route();
		} else {
			filter.find('a').route();
		}

		// Add an active state to the parent
		self.setActiveFilter(filter);

		// If this is list filter, we generate the playlist player
		if (type == 'list') {
			playlistId = filter.data('id');

			self.controller.getPlaylist(playlistId, function() {
				filter.removeClass('is-loading');
				filter.parent().removeClass('is-loading');
			});
			return;
		}

		// Filter by genre
		var genreId = null;

		if (type == 'genre') {
			type = 'all';
			genreId = filter.data('id');
		}

		var hashtagId = null;

		if (type == 'hashtag') {
			hashtagId = filter.data('tagId');
		}

		// Set the current filter
		self.controller.currentFilter = type;
		self.controller.genreId = genreId;
		self.controller.isSort = false;
		self.controller.hashtagId = hashtagId;

		self.controller.result().empty();
		self.controller.wrapper().addClass('is-loading');

		self.controller.getAudios(function() {

			filter.removeClass('is-loading');
			filter.parent().removeClass('is-loading');
			self.filter().parent().removeClass('active');
		});

		self.controller.trigger('onEasySocialFilterClick');
	},

	"{self} updateListCounters": function() {

		EasySocial.ajax('site/controllers/audios/getListCounts')
		.done(function(lists) {

			$(lists).each(function(i, list){
				self.filter('[data-type="list"][data-id="' + list.id + '"]')
					.siblings(self.counters.selector)
					.html(list.count);
			});
		});
	},

	"{createFilter} click": function(filter, event) {

		// Prevent default
		event.preventDefault();
		event.stopPropagation();

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/audios/getFilterFormDialog', {
				"id": filter.data('id'),
				"cid": filter.data('uid'),
				"clusterType": filter.data('clusterType')
			})
		});
	}
}});

module.resolve();
});


});
