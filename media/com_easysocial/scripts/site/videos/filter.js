EasySocial.module('site/videos/filter', function($) {

var module = this;

EasySocial.require()
.script('site/videos/browser')
.done(function($) {


EasySocial.Controller('Videos.Filter', {

defaultOptions: {
	"{filter}": "[data-es-video-filters] [data-filter-item]",
	"{createFilter}": "[data-video-create-filter]",
	"{browserController}": "[data-videos-listing]"
}

}, function(self, opts, base) { return {

	controller: null,
	clicked: false,

	init: function() {
		self.controller = self.getController();

		if (self.controller) {
			self.controller.activeFilter = self.filter('[data-type=' + opts.active + ']');
		}
	},

	getController: function() {
		var controller = self.browserController().controller();

		return controller;
	},

	setActiveFilter: function(filter) {

		self.filter().removeClass('active');

		filter.addClass('active');

		// Set loading on the correct filter
		filter.addClass('is-loading');
	},

	"{filter} click": function(filter, event) {

		// Since controller doesn't exist, we should just redirect to the view
		if (!self.controller) {
			return;
		}

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

		// Filter by category
		var categoryId = null;

		if (type == 'category') {
			type = 'all';
			categoryId = filter.data('id');
		}

		var hashtagId = null;

		if (type == 'hashtag') {
			hashtagId = filter.data('tagId');
		}

		// Set the current filter
		self.controller.currentFilter = type;
		self.controller.categoryId = categoryId;
		self.controller.hashtagId = hashtagId;
		self.controller.isSort = false;

		self.controller.result().empty();
		self.controller.wrapper().addClass('is-loading');

		self.controller.getVideos(function() {
			filter.removeClass('is-loading');
		});

		// trigger sidebar toggle for responsive view.
		self.controller.trigger('onEasySocialFilterClick');
	},

	"{createFilter} click": function(filter, event) {

		// Prevent default
		event.preventDefault();
		event.stopPropagation();

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/videos/getFilterFormDialog', {
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
