EasySocial.module('site/groups/filter', function($) {

var module = this;

EasySocial.require()
.script('site/groups/browser')
.done(function($) {

EasySocial.Controller('Groups.Filter', {
	defaultOptions: {
		"{listController}": "[data-es-structure] [data-es-groups]",
		"{filter}": "[data-es-group-filters] [data-filter-item]",
		"{osmMap}": "[data-osm-map]"
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

	filterNearby: function(filter) {

		// Claer the contents in the controller
		var controller = self.getController();

		controller.clearContents(false);

		// Try to get the location first
		controller.element.addClass('is-detecting-location');

		if (self.osmMap().length > 0) {
			self.locateOsm();
		} else {
			self.locateGmaps();
		}
	},

	locateOsm: function() {
		EasySocial.require()
		.library('leaflet')
		.done(function() {
			if (self.osm === undefined) {
				self.osm = L.map('map');
			}
			self.osm.locate();
			self.osm.on('locationfound', function(e) {
				self.getController().element.removeClass('is-detecting-location');
				self.getController().setLocation(e.latitude, e.longitude);

				self.getController().getItems(false, function() {
					self.filter().removeClass('is-loading');
				});
			});

			self.osm.on('locationerror', function(e) {
				self.filter().removeClass('is-loading');

				self.getController().location().addClass('t-text--danger');
				self.getController().location().find('>i').addClass('t-text--danger');

				self.getController().locationMessage().html(e.message);
			});
		});
	},

	locateGmaps: function() {
		EasySocial.require()
		.library('gmaps')
		.done(function() {

			var controller = self.getController();
			var filter = self.filter();

			$.GMaps.geolocate({
				success: function(position) {
					controller.element.removeClass('is-detecting-location');
					controller.setLocation(position.coords.latitude, position.coords.longitude);

					controller.getItems(false, function() {
						filter.removeClass('is-loading');
					});
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
			return self.filterNearby(filter);
		}

		// Claer the contents in the controller
		controller.clearContents(true);

		controller.getItems(false, function() {
			filter.removeClass('is-loading');
		});
	}
}});

module.resolve();

});

});
