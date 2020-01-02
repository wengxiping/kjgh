EasySocial.module('site/activities/filter', function($) {

var module = this;

EasySocial.require()
.script('site/activities/default')
.done(function($) {

EasySocial.Controller('Activities.Filter', {
	defaultOptions: {
		"{listController}": "[data-es-structure] [data-activities]",
		"{filter}": "[data-es-activities-filters] [data-sidebar-item]",
	}
}, function(self, opts) { return {

	getController: function() {
		var controller = self.listController().controller();

		return controller;
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

		var controller = self.getController();

		if (controller === undefined) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		var filterType = filter.data('type');

		// Set active filter state
		self.setActiveFilter(filter);
		controller.setActiveFilter(filter);

		// Notify the dashboard that it's starting to fetch the contents.
		controller.updatingContents();

		// Fetch the items
		controller.getItems(filterType, function() {
			filter.removeClass('is-loading');
		});
	}
}});

module.resolve();

});

});
