EasySocial.module("site/locations/osm", function($){

var module = this;

EasySocial
.require()
.library("scrollTo", "image", "leaflet", "leaflet-providers")
.done(function($) {

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

EasySocial.Controller("OSM", {
	defaultOptions: {

		latitude: null,
		longitude: null,
		enableMarker: true,
		mapElementId: 'map',

		"{textField}": "[data-location-textField]",
		"{detectLocationButton}": "[data-detect-location-button]",

		'{autocomplete}': '[data-location-autocomplete]',
		"{suggestions}": "[data-location-suggestions]",
		"{suggestion}": "[data-story-location-suggestion]",

		"{latitude}" : "[data-location-lat]",
		"{longitude}": "[data-location-lng]",

		"{removeButton}": "[data-location-remove-button]"
	}
}, function(self, opts, base) { return {

	init: function() {

		if (self.options.mapElementId) {
			self.mapElementId = self.options.mapElementId;
		}

		// Only show auto-detect button if the browser supports geolocation
		if (navigator.geolocation && window.es.isHttps) {
			self.detectLocationButton().removeClass('t-hidden');
			self.detectLocationButton().show();
		}

		// Allow textfield input only when controller is implemented
		EasySocial
			.require()
			.library("leaflet")
			.done(function(){
				self.textField().removeAttr("disabled");
			});


		// If caller specified a latitude or longitude, navigate the map
		setTimeout(function() {
			if (opts.latitude && opts.longitude) {
				if (self.osm === undefined) {
					self.initMap();
				}

				self.navigate(opts.latitude, opts.longitude);

				self.detectLocationButton().hide();
			}
		}, 1);
	},

	initMap: function() {

		self.osm = L.map(self.mapElementId, {
			zoom: 12,
			attributionControl: false,
			zoomControl: false
		});

		// self.osm.fitWorld();

		L.tileLayer.provider('Wikimedia').addTo(self.osm);

		// Allow textField input only when controller is implemented
		self.textField().removeAttr("disabled");

		if (self.options.enableMarker) {
			function onMapClick(e) {
				var latlng = e.latlng;

				self.osm.removeLayer(self.marker);
				self.marker = L.marker(latlng).addTo(self.osm);
				self.osm.setView(latlng);
				self.lookupLatLng(latlng);
			}

			self.osm.on('click', onMapClick);
		}
	},

	deactivate: function() {
		// This should allow caller to deactivate the suggestions
	},

	navigate: function(lat, lng) {
		var latlng = {
					lat: parseFloat(lat),
					lng: parseFloat(lng)
				}

		if (self.marker !== undefined) {
			self.osm.removeLayer(self.marker);
		}

		self.osm.setView(latlng, 10);

		self.marker = L.marker(latlng).addTo(self.osm);

		base.addClass("has-location");
	},

	// Memoized locations
	locations: {},

	lastQueryAddress: null,

	"{textField} keypress": function(textField, event) {

		switch (event.keyCode) {

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

				// self.suggestions().hide();
				break;

			case KEYCODE.ESCAPE:
				break;
		}

	},

	"{textField} keyup": function(textField, event) {

		switch (event.keyCode) {

			case KEYCODE.UP:
			case KEYCODE.DOWN:
			case KEYCODE.ENTER:
			case KEYCODE.ESCAPE:
				// Don't repopulate if these keys were pressed.
				break;

			default:
				var address = $.trim(textField.val());

				if (address==="") {
					// self.suggestions().hide();

				}

				if (address==self.lastQueryAddress) {
					return;
				}

				var locations = self.locations[address];

				// If this location has been searched before
				if (locations) {

					// Just use cached results
					self.suggest(locations);

					// And set our last queried address to this address
					// so that it won't repopulate the suggestion again.
					self.lastQueryAddress = address;

				// Else ask google to find it out for us
				} else {

					self.lookup(address);
				}
				break;
		}
	},

	lookupLatLng: $.debounce(function(latlng) {

		self.element.addClass("is-loading");

		EasySocial.ajax('site/controllers/location/getLocations', {
			latitude: latlng.lat,
			longitude: latlng.lng
		})
		.done(function(results) {
			result = results[0];
			result.address = result.name;
			self.element.removeClass("is-loading");
			self.set(result);
		});
	}, 250),

	lookup: $._.debounce(function(address){

		self.element.addClass("is-loading");

		EasySocial.ajax('site/controllers/location/suggestLocations', {
			"address": address,
		}).done(function(locations) {

			// Store a copy of the results
			self.locations[address] = locations;

			self.suggest(locations);
			self.textField().focus();
			self.element.addClass("has-suggested");
			self.element.removeClass("is-loading");
		});

	}, 250),

	suggest: function(locations) {

		var suggestions = self.suggestions();

		// Clear location suggestions
		suggestions.empty();

		var items = [];

		$.each(locations, function(i, location) {
			items.push(location.formatted_address);
		});

		EasySocial.ajax('site/views/location/format', {
			"locations": items
		}).done(function(rows) {

			$.each(rows, function(i, row) {

				$(row)
					.data('location', locations[i])
					.appendTo(suggestions);
			});
		});

		self.autocomplete().addClass("active");
	},

	"{suggestion} activate": function(suggestion, event) {
		if (self.osm === undefined) {
			self.initMap();
		}

		var location = suggestion.data("location");

		self.navigate(location.latitude, location.longitude);
	},

	"{suggestion} mouseover": function(suggestion) {

		// Remove all active class
		self.suggestion().removeClass("active");

		suggestion
			.addClass("active")
			.trigger("activate");
	},

	"{suggestion} click": function(suggestion, event) {

		if (self.osm === undefined) {
			self.initMap();
		}

		var location = suggestion.data("location");

		self.set(location);

		// Remove active class on the auto complete
		self.autocomplete().removeClass('active');

		// Hide the suggestions list
		// self.suggestions().hide();
		self.element.removeClass("has-suggested");
	},

	set: function(location) {

		self.currentLocation = location;

		location.fulladdress = location.name;
		location.address = location.name;

		self.navigate(location.latitude, location.longitude);

		// Set the address on the field
		self.textField().val(location.fulladdress);

		self.latitude()
			.val(location.latitude);

		self.longitude()
			.val(location.longitude);

		self.detectLocationButton().hide();

		self.trigger("locationChange", [location]);

		self.lastQueryAddress = location.address;
	},

	unset: function() {
		self.currentLocation = null;

		self.textField().val('');

		self.element.removeClass("has-location");

		self.detectLocationButton().show();
	},

	"{detectLocationButton} click": function() {

		self.element.addClass("is-loading");

		if (self.osm === undefined) {
			self.initMap();
		}

		self.osm.locate();
		self.osm.on('locationfound', function(e) {
			self.detectLocationButton().removeClass('is-loading');
			lat = e.latitude,
			lng = e.longitude;

			self.navigate(lat, lng);

			EasySocial.ajax('site/controllers/location/getLocations', {
				latitude: lat,
				longitude: lng
			})
			.done(function(results) {
				result = results[0];
				result.address = result.name;

				self.element.removeClass("is-loading");

				self.set(result);

				// Remove active class on the auto complete
				self.autocomplete().removeClass('active');

				// Hide the suggestions list
				self.suggestions().hide();
				self.element.removeClass("has-suggested");
			});
		});

	},

	"{removeButton} click": function(removeButton, event) {
		self.unset();
	}

}});

// Resolve module
module.resolve();

});

});
