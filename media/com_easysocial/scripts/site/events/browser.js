EasySocial.module('site/events/browser', function($) {

var module = this;

EasySocial.require()
.done(function($) {


EasySocial.Controller('Events.Browser', {
	defaultOptions: {

		// Filters
		"{filterItem}": "[data-filter-item]",
		"{ordering}": "[data-ordering]",

		// Contents
		'{contents}': '[data-contents]',
		'{list}': '[data-events-list]',
		'{wrapper}': '[data-wrapper]',
		'{subWrapper}': '[data-sub-wrapper]',

		// Fetching location wrapper
		"{location}": "[data-fetching-location]",
		"{locationMessage}": "[data-detecting-location-message]",
		"{osmMap}": "[data-osm-map]",

		// Date navigation
		"{navigate}": "[data-navigation-date]",

		// Calendar
		'{calendar}': '[data-events-calendar]',
		"{calendarWrapper}": "[data-events-calendar-wrapper]",

		// Include past events options
		'{includePastCheckbox}': '[data-events-past]',
		'{includePastLink}': '[data-include-past-link]',

		// Hide repetition events options
		'{hideRepetitionCheckbox}': '[data-events-repetition]',
		'{hideRepetitionLink}': '[data-hide-repetition-link]',

		// Distance searches
		'{radius}': '[data-radius]',

		filter: null,
		categoryId: 0,
		delayed: false,
		includePast: false,
		ordering: 'start',
		hasLocation: false,
		userLatitude: '',
		userLongitude: '',
		distance: 10,
		group: null,
		page: null,
		isModule: false,
		clusterId: null
	}
}, function(self, opts) { return {

	init: function() {
		opts.filter = self.element.data('filter');
		opts.categoryId = self.element.data('categoryid');
		opts.clusterId = self.element.data('clusterid');

		self.isUpdating = false;

		if (self.options.delayed) {
			self.delayedInit();
		}
	},

	delayedInit: function() {
		// It is possible that view is flagging it as "delayed" in order for javascript to make an ajax call to retrieve the data instead

		// delayed init will have some preset parameter coming from url, hence we don't use the filterbynearby method

		if (opts.filter === 'nearby') {
			self.filterEventsNearby();
		}
	},

	setActiveFilter: function(filterItem) {
		self.filterItem().removeClass('active');
		filterItem.addClass('active is-loading');

		self.activeFilter = filterItem;
	},

	setLocation: function(latitude, longitude) {
		opts.latitude = latitude;
		opts.longitude = longitude;
	},

	setUserId: function(userId) {
		opts.userId = userId;
	},

	setFilter: function(filter) {
		opts.filter = filter;
	},

	setCategoryId: function(categoryId) {
		opts.categoryId = categoryId;
	},

	clearContents: function(showLoading) {
		var showLoading = showLoading === undefined ? true : showLoading;

		self.element.removeClass('is-detecting-location');
		self.contents().empty();

		if (showLoading) {
			self.wrapper().addClass('is-loading');
		}
	},

	updatingContents: function() {
		self.contents().empty();
		self.wrapper().addClass('is-loading');
		self.element.removeClass('is-detecting-location');
	},

	updateContents: function(html) {
		self.wrapper().removeClass('is-loading');

		if (self.activeFilter) {
			self.activeFilter.removeClass('is-loading');
		}

		self.contents().html(html);
	},

	updatingListing: function() {
		self.list().empty();
		self.subWrapper().addClass('is-loading');
	},

	updateListing: function(html) {

		self.subWrapper().removeClass('is-loading');
		self.list().html(html);
	},

	updateIncludePastLink: function(link) {
		self.includePastLink().attr('href', link);
	},

	updateHideRepetitionLink: function(link) {
		self.hideRepetitionLink().attr('href', link);
	},


	setSortLink: function(type, link) {
		var sortUrl = $('a[data-ordering="' + type + '"]');

		if (sortUrl.length > 0) {
			sortUrl.attr('href', link);
		}
	},

	getEvents: function(isSorting, callback) {
		// Include past
		var includePast = opts.includePast ? 1 : 0;

		// Include past
		var hideRepetition = opts.hideRepetition ? 1 : 0;

		// When user clicked on My Event filter
		// We will always include past event
		if ((opts.filter == 'mine' || opts.filter == 'createdbyme') && isSorting === false) {
			includePast = 1;
		}

		EasySocial.ajax('site/controllers/events/filter', {
			"type": opts.filter,
			"date": opts.date,
			"categoryId": opts.categoryId,
			"sort": isSorting ? 1 : 0,
			"ordering": opts.ordering,
			"includePast": includePast,
			"hideRepetition": hideRepetition,
			"latitude": opts.userLatitude,
			"longitude": opts.userLongitude,
			"distance": opts.distance,
			"clusterId": opts.clusterId,
			"activeUserId": opts.activeUserId,
			"browseView": opts.browseView
		}).done(function(contents, includePastUrl, hideRepetitionUrl, recentSortingUrl, startSortingUrl, distanceSortingUrl) {

			if ($.isFunction(callback)) {
				callback.call(this, contents, includePastUrl, hideRepetitionUrl, recentSortingUrl, startSortingUrl, distanceSortingUrl);
			}

			if (isSorting) {

				// update all the sorting urls.
				self.updateSortingUrls(includePastUrl, hideRepetitionUrl, recentSortingUrl, startSortingUrl, distanceSortingUrl);

				self.updateListing(contents);
				return;
			}

			self.updateContents(contents);

			$('body').trigger('afterUpdatingContents', [contents]);

			if (!isSorting) {
				// trigger sidebar toggle for responsive view.
				self.trigger('onEasySocialFilterClick');
			}
		}).always(function() {
			self.isUpdating = false;
		});
	},

	filterEventsNearby: function() {

		// Set loading indicator
		self.updatingListing();

		// Ensure that we already have the necessary location values
		if (opts.hasLocation && opts.userLatitude && opts.userLongitude) {
			return self.getEvents(false);
		}

		// Try to get the location first
		self.element.addClass('is-detecting-location');
		self.contents().empty();

		if (self.osmMap().length > 0) {
			self.locateOsm();
		} else {
			self.locateGmaps();
		}
	},

	locateOsm: function () {
		EasySocial.require()
		.library('leaflet')
		.done(function() {
			if (self.osm === undefined) {
				self.osm = L.map('map');
			}
			self.osm.locate();
			self.osm.on('locationfound', function(e) {
				self.element.removeClass('is-detecting-location');

				opts.userLatitude = e.latitude;
				opts.userLongitude = e.longitude;

				opts.hasLocation = true;

				return self.getEvents(false);
			});

			self.osm.on('locationerror', function(e) {
				self.filterItem().removeClass('is-loading');
				self.location().addClass('t-text--danger');
				self.location().find('>i').addClass('t-text--danger');
				self.locationMessage().html(e.message);
			});
		});
	},

	locateGmaps: function() {
		EasySocial.require()
		.library('gmaps')
		.done(function() {
			$.GMaps.geolocate({
				success: function(position) {

					self.element.removeClass('is-detecting-location');

					opts.userLatitude = position.coords.latitude;
					opts.userLongitude = position.coords.longitude;

					opts.hasLocation = true;

					return self.getEvents(false);
				},
				error: function(error) {
					self.filterItem().removeClass('is-loading');
					self.location().addClass('t-text--danger');
					self.location().find('>i').addClass('t-text--danger');
					self.locationMessage().html(error.message);
				}
			});
		});
	},

	"{filterItem} click": function(filterItem, event) {
		event.preventDefault();
		event.stopPropagation();

		if (self.isUpdating) {
			return;
		}

		self.isUpdating = true;

		// Find the anchor for the filter
		var anchor = filterItem.find('> a');
		anchor.route();

		// Set active and loading states
		self.setActiveFilter(filterItem);

		// Reset the options
		opts.date = false;
		opts.filter = filterItem.data('type');
		opts.categoryId = filterItem.data('id');

		// If this is filtering by nearby we need to get the user coordinates
		if (opts.filter == 'nearby') {
			return self.filterEventsNearby();
		}

		// If it's not filtering by nearby, we need to update the ordering accordingly.
		if (opts.filter != 'nearby') {
			opts.ordering = 'start';
		}

		// Set loading indicator
		self.updatingContents();

		// Get the events
		self.getEvents(false);
	},

	'{ordering} click': function(button, event) {
		// Remove active classes on the button
		self.ordering().removeClass('active');
		button.addClass('active');

		opts.ordering = button.data('ordering');

		// here we need to update the includePast or hideRepetitive Events or not.
		opts.includePast = self.includePastCheckbox().is(':checked');
		opts.hideRepetition = self.hideRepetitionCheckbox().is(':checked');

		// Route now to set the correct url
		button.route();

		// Set loading indicator
		self.updatingListing();

		// Get the events
		self.getEvents(true);
	},

	updateSortingUrls: function(includePastUrl, hideRepetitionUrl, recentSortingUrl, startSortingUrl, distanceSortingUrl) {

		self.updateIncludePastLink(includePastUrl);
		self.updateHideRepetitionLink(hideRepetitionUrl);

		self.setSortLink('recent', recentSortingUrl);
		self.setSortLink('start', startSortingUrl);
		self.setSortLink('distance', distanceSortingUrl);
	},

	toggleOrdering: function() {
		self.ordering().parents('[data-event-sorting]').toggleClass('t-hidden', opts.includePast);
	},

	'{includePastCheckbox} change': function(checkbox, event) {

		if (self.isUpdating) {
			return;
		}

		self.isUpdating = true;
		self.includePastCheckbox().attr('disabled', 'disabled');

		opts.includePast = checkbox.is(':checked');

		// Route the include past link
		self.includePastLink().route();

		// Set loading indicator
		self.updatingListing();

		// Show or Hide the sorting button
		self.toggleOrdering();

		// Get the events
		self.getEvents(true, function() {
			self.includePastCheckbox().removeAttr('disabled');
		});
	},

	'{hideRepetitionLink} click': function(link, event) {
		event.preventDefault();
		event.stopPropagation();

		self.hideRepetitionCheckbox().trigger('click');
	},

	'{hideRepetitionCheckbox} change': function(checkbox, event) {

		if (self.isUpdating) {
			return;
		}

		self.isUpdating = true;
		self.hideRepetitionCheckbox().attr('disabled', 'disabled');

		opts.hideRepetition = checkbox.is(':checked');

		// Route the include past link
		self.hideRepetitionLink().route();

		// Set loading indicator
		self.updatingListing();

		// Get the events
		self.getEvents(true, function() {
			self.hideRepetitionCheckbox().removeAttr('disabled');
		});
	},

	'{includePastLink} click': function(link, event) {
		event.preventDefault();
		event.stopPropagation();

		self.includePastCheckbox().trigger('click');
	},



	'{navigate} click': function(link, event) {
		event.preventDefault();
		event.stopPropagation();

		if (self.isUpdating) {
			return;
		}

		self.isUpdating = true;


		// Route the link
		link.route();

		// Set the filter
		opts.filter = 'date';
		opts.date = link.data('navigation-date');

		// Set loading indicator
		self.updatingListing();

		self.getEvents(false);
	},

	'{radius} click': function(dropdown, event) {

		// Get the distance
		opts.distance = dropdown.data('radius');
		opts.ordering = 'distance';

		// Set loading indicator
		// since added to show feature events in nearby event filter then have to refresh the whole content
		self.updatingContents();

		// Update the listing
		self.getEvents(false, function(contents, includePastUrl, hideRepetitionUrl, recentSortingUrl, startSortingUrl, distanceSortingUrl) {

			// Update the current url
			History.pushState({state:1}, document.title, distanceSortingUrl);

			// Update the include past link
			self.updateIncludePastLink(includePastUrl);

			// update hide repetitive event link
			self.updateHideRepetitionLink(includePastUrl);
		});
	}
}});

module.resolve();
});

});
