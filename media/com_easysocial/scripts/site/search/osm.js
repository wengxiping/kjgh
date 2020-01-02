EasySocial.module('site/search/osm', function($){

var module = this;

// Create search template first
$.template('easysocial/maps.suggestion', '<div class="es-location-suggestion" data-location-suggestion><span class="formatted_address">[%= location.formatted_address %]</span></div>');

EasySocial.require()
.library('leaflet', 'leaflet-providers')
.done(function() {

// Constants
var KEYCODE = {
	BACKSPACE: 8,
	COMMA: 188,
	DELETE: 46,
	DOWN: 40,
	ENTER: 13,
	ESCAPE: 27,
	LEFT: 37,
	RIGHT: 39,
	SPACE: 32,
	TAB: 9,
	UP: 38
};

EasySocial.Controller('Search.Osm', {
	defaultOptions: {
		"{icon}" : "[data-loaction-icon]",
		"{locationLabel}" : "[data-location-label]",
		'{textField}'       : '[data-location-textfield]',

		"{detectButton}" : "[data-location-detect]",
		"{suggestions}"  : "[data-location-suggestions]",
		"{suggestion}"      : "[data-location-suggestion]",
		"{autocomplete}" : "[data-location-autocomplete]",

		// form elements
		"{dataCondition}" : "[data-condition]",
		"{frmDistance}" : "[data-distance]",
		"{frmAddress}" : "[data-address]",
		"{frmLatitude}" : "[data-latitude]",
		"{frmLongitude}" : "[data-longitude]",

		// error messages
		"{errorPermission}": "[data-error-map-permission]",
		"{errorTimeout}": "[data-error-map-timeout]",
		"{errorUnavailable}": "[data-error-map-unavailable]",

		view: {
			suggestion: 'maps.suggestion'
		}
	}
}, function(self, opts, base){ return {

	init : function() {
	},

	locations: {},

	lastQueryAddress: null,

	results: [],

	result: null,

	initMap: function() {
		self.osm = L.map('map', {
			zoom: 12
		});
	},

	"{detectButton} click": function() {
		self.detectButton().addClass('is-loading');

		if (self.osm == undefined) {
			self.initMap();
		}

		clearTimeout(self.detectTimer);

		self.osm.locate();
		self.osm.on('locationfound', function(e) {
			lat = e.latitude,
			lng = e.longitude;

			EasySocial.ajax('site/controllers/location/getLocations', {
				latitude: lat,
				longitude: lng
			})
			.done(function(results) {
				self.set(results[0]);

				clearTimeout(self.detectTimer);
				self.detectButton().removeClass('is-loading');
			});
		});

		self.osm.on('locationerror', function(error) {
			var message = "";

			switch (error.code) {

				case 1:
					message = self.errorPermission().text();
					break;

				case 2:
					message = self.errorTimeout().text();
					break;

				case 3:
				default:
					message = self.errorUnavailable().text();
					break;
			}

			EasySocial.dialog({
				content: message
			});
		});


	},

	lookup: $.debounce(function(address) {
		self.detectButton().addClass('is-loading');

		EasySocial.ajax('site/controllers/location/getLocations', {
			query: address
		})
		.done(function(locations) {
			// Store a copy of the results
			self.locations[address] = locations;

			// Suggestion locations
			self.suggest(locations);

			self.lastQueryAddress = address;

			self.detectButton().removeClass('is-loading');
		});
	}, 250),


	"{textField} keypress": function(textField, event) {

		switch (event.keyCode)
		{
			case KEYCODE.UP:

				var prevSuggestion = $(
					self.suggestion(".active").prev(self.suggestion.selector)[0] ||
					self.suggestion(":last")[0]
				);

				// Remove all active class
				self.suggestion().removeClass("active");

				prevSuggestion
					.addClass("active")
					.trigger("activate");

				self.suggestions()
					.scrollTo(prevSuggestion, {
						offset: prevSuggestion.height() * -1
					});

				event.preventDefault();

				break;

			case KEYCODE.DOWN:

				var nextSuggestion = $(
					self.suggestion(".active").next(self.suggestion.selector)[0] ||
					self.suggestion(":first")[0]
				);

				// Remove all active class
				self.suggestion().removeClass("active");

				nextSuggestion
					.addClass("active")
					.trigger("activate");

				self.suggestions()
					.scrollTo(nextSuggestion, {
						offset: nextSuggestion.height() * -1
					});

				event.preventDefault();

				break;

			case KEYCODE.ENTER:

				var activeSuggestion = self.suggestion(".active"),
					location = activeSuggestion.data("location");
					self.set(location);

				self.hideSuggestions();

				event.preventDefault();
				break;

			case KEYCODE.ESCAPE:
				self.hideSuggestions();
				event.preventDefault();
				break;
		}

	},

	"{textField} keyup": function(textField, event) {

		switch (event.keyCode) {

			case KEYCODE.UP:
			case KEYCODE.DOWN:
			case KEYCODE.LEFT:
			case KEYCODE.RIGHT:
			case KEYCODE.ENTER:
			case KEYCODE.ESCAPE:
				// Don't repopulate if these keys were pressed.
				break;

			default:
				var address = $.trim(textField.val());

				if (address==="") {
					// self.base().removeClass("has-location");
					self.hideSuggestions();
				}

				// if (address==self.lastQueryAddress) return;

				var locations = self.locations[address];

				// If this location has been searched before
				if (locations) {

					// And set our last queried address to this address
					// so that it won't repopulate the suggestion again.
					self.lastQueryAddress = address;

					// Just use cached results
					self.suggest(locations);

				// Else ask google to find it out for us
				} else {

					self.lookup(address);
				}
				break;
		}
	},

	set: function(location) {

		var lat = location.latitude,
			lng = location.longitude,
			address = location.formatted_address,
			distance = self.frmDistance().val();

		self.frmAddress().val(address);
		self.frmLatitude().val(lat);
		self.frmLongitude().val(lng);

		var computedVal = distance + '|' + lat + '|' + lng + '|' + address;
		self.dataCondition().val(computedVal);

		self.textField().val(address);
		// self.locationLabel().removeClass('hide');

		self.hideSuggestions();

	},

	"{suggestion} click": function(suggestion, event) {
		var location = suggestion.data("location");
		self.set(location);
	},

	suggest: function(locations) {

		self.hideSuggestions();

		var suggestions = self.suggestions();

		if (locations.length < 0) return;

		self.results = locations;

		$.each(locations, function(i, location){
			// Create suggestion and append to list
			self.view.suggestion({
					location: location
				})
				.data("location", location)
				.appendTo(suggestions);
		});

		self.showSuggestions();
	},

	showSuggestions: function() {

		self.focusSuggestion = true;

		setTimeout(function(){

			self.autocomplete().addClass("active");

			var doc = $(document),
				hideOnClick = "click.es.advancedsearch.location";

			doc
				.off(hideOnClick)
				.on(hideOnClick, function(event){

					// Collect list of bubbled elements
					var targets = $(event.target).parents().andSelf();

					if (targets.filter(self.element).length > 0) return;

					doc.off(hideOnClick);

					self.hideSuggestions();
				});

		}, 500);
	},

	hideSuggestions: function() {

		// Clear location suggestions
		self.suggestions().empty();

		self.focusSuggestion = false;

		self.autocomplete().removeClass("active");

		$(document).off("click.es.advancedsearch.location");

	}
}});

module.resolve();

});

});
