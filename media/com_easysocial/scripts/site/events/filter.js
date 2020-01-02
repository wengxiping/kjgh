EasySocial.module('site/events/filter', function($) {

var module = this;

EasySocial.require()
.script('site/events/browser', 'site/events/calendar')
.done(function($) {

EasySocial.Controller('Events.Filter', {
	defaultOptions: {
		"{listController}": "[data-es-structure] [data-es-events]",
		"{filter}": "[data-es-event-filters] [data-filter-item]",

		// Calendar
		'{calendar}': '[data-events-calendar]',
		"{calendarWrapper}": "[data-events-calendar-wrapper]",

		// Calendar in module
		'{calendarModule}': '[data-events-calendar-module]'
	}
}, function(self, opts) { return {

	init: function() {
		opts.filter = self.element.data('filter');
		opts.categoryId = self.element.data('categoryid');
		opts.clusterId = self.element.data('clusterid');

		self.renderCalendar();
	},

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

	filterNearby: function() {

		// Claer the contents in the controller
		var controller = self.getController();

		controller.clearContents(false);

		// Try to get the location first
		controller.element.addClass('is-detecting-location');

		EasySocial.require()
		.library('gmaps')
		.done(function() {

			$.GMaps.geolocate({
				success: function(position) {
					self.filter().removeClass('is-loading');

					controller.element.removeClass('is-detecting-location');
					controller.setLocation(position.coords.latitude, position.coords.longitude);

					controller.getEvents();
				},

				error: function(error) {

					self.filter().removeClass('is-loading');

					controller.location().addClass('t-text--danger');
					controller.location().find('>i').addClass('t-text--danger');

					controller.locationMessage().html(error.message);
				}
			});
		});
	},

	renderCalendar: function() {
		self.calendarWrapper().addClass('is-loading');

		EasySocial.ajax('site/views/events/renderCalendar', {
			"filter": opts.filter,
			"categoryId": opts.categoryId,
			"clusterId": opts.clusterId
		}).done(function(html) {

			var calendar = self.calendar();

			if (opts.isModule) {
				calendar = self.calendarModule();
			}

			calendar
				.html(html)
				.addController('EasySocial.Controller.Events.Browser.Calendar', {
					'{parent}': self.getController(),
					calendarWrapper: self.calendarWrapper(),
					isModule: opts.isModule
				});

			calendar.trigger('calendarLoaded');
		});
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

		controller.setFilter(filterType);

		controller.setCategoryId(null);

		if (filterType == 'category') {
			controller.setCategoryId(filter.data('id'));
		}

		// If this is filtering by nearby we need to get the user coordinates
		if (filterType == 'nearby' && !opts.latitude && !opts.longitude) {
			return self.filterNearby();
		}

		// Claer the contents in the controller
		controller.clearContents(true);

		controller.getEvents(false, function() {
			filter.removeClass('is-loading');
		});
	}
}});

module.resolve();

});
});
