EasySocial.module("site/story/osm", function($){

var module = this;

EasySocial.require()
.library("leaflet", "scrollTo", "image", "leaflet-providers")
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

EasySocial.Controller("Story.Osm", {
	defaultOptions: {

		map: {
			lat: 0,
			lng: 0
		},

		mapElementId: 'map',

		"{base}": "[data-story-location]",
		"{form}": "[data-story-location-form]",

		"{textField}": "[data-story-location-textField]",
		"{detectButton}": "[data-story-location-detect-button]",

		"{autocomplete}": "[data-story-location-autocomplete]",
		"{suggestions}": "[data-story-location-suggestions]",
		"{suggestion}": "[data-story-location-suggestion]",

		"{textbox}": "[data-story-location-textbox]",
		"{removeButton}": "[data-story-location-remove-button]",

		"{map}": "[data-story-location-map]",
		"{mapImage}": "[data-story-location-map-image]",

		"{meta}": "[data-story-meta-location]",
	}
}, function(self, opts) { return {

	init: function() {

		var currentLocation = self.options.currentLocation;

		if (self.options.mapElementId) {
			self.mapElementId = self.options.mapElementId;
		}

		// Only show auto-detect button if the browser supports geolocation
		if (navigator.geolocation && window.es.isHttps) {
			self.base().addClass("is-detectable");
			self.detectButton().show();
		}

		// Add placeholder support for IE9
		self.textField().placeholder();

		// Allow textfield input only when controller is implemented
		self.textField().removeAttr("disabled");

		if (currentLocation) {
			self.story.activateMeta('location');
			self.initMap();
			self.set(currentLocation);
		}
	},

	initMap: function() {

		self.osm = L.map(self.mapElementId, {
			zoom: 12
		});

		self.osm.fitWorld();

		// Esri.WorldImagery | HikeBike.HikeBike | CartoDB.Voyager | OpenStreetMap.Mapnik
		L.tileLayer.provider('Wikimedia').addTo(self.osm);

		// Add placeholder support for IE9
		self.textField().placeholder();

		// Allow textField input only when controller is implemented
		self.textField().removeAttr("disabled");

		function onMapClick(e) {
			var latlng = e.latlng;

			self.osm.removeLayer(self.marker);
			self.marker = L.marker(latlng).addTo(self.osm);
			self.osm.setView(latlng);
			self.lookupLatLng(latlng);
		}

		self.osm.on('click', onMapClick);
	},

	"{window} resize": $.debounce(function() {

		var currentLocation = self.currentLocation;

		if (!currentLocation) return;

		var mapImage = self.mapImage();

		if (mapImage.data("width") !== mapImage.width()) {
			self.navigate(currentLocation.latitude, currentLocation.longitude);
		}

	}, 250),

	updateField: function(result) {

		// Fill in the field with address
		self.textField().val(result[0].name);

		result = result[0];

		var location = {
			latitude: result.latitude,
			longitude: result.longitude,
			fulladdress: result.formatted_address,
			formatted_address: result.formatted_address,
			name: result.formatted_address,
			address: result.address
		}

		self.currentLocation = location;

		var process = $.Deferred();

		process.resolve(location);

		process.done(function(location) {

			// Get the caption
			EasySocial.ajax('site/views/location/getStoryCaption', {
				"address": result.name
			}).done(function(caption) {
				self.story.setMeta("location", caption);
			})

			self.lastQueryAddress = location.fulladdress;

			self.base()
				.removeClass('is-loading')
				.addClass("has-location");
		});
	},


	navigate: function(lat, lng) {
		self.detectButton().addClass("t-hidden");

		var latlng = {
					lat: parseFloat(lat),
					lng: parseFloat(lng)
				}

		if (self.marker !== undefined) {
			self.osm.removeLayer(self.marker);
		}

		self.osm.flyTo(latlng, 10, {
			"duration": 3
		});

		self.marker = L.marker(latlng).addTo(self.osm);
	},

	// Memoized locations
	locations: {},

	lastQueryAddress: null,

	"{textField} keydown": function(textField, event) {

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

					if (location !== undefined) {
						self.set(location);
					}

				self.hideSuggestions();
				break;

			case KEYCODE.ESCAPE:
				self.hideSuggestions();
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

				if (address === "") {
					self.base().removeClass("has-location");
					self.hideSuggestions();
				}

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

	lookupLatLng: $.debounce(function(latlng) {

		self.base().addClass("is-loading");

		EasySocial.ajax('site/controllers/location/getLocations', {
			latitude: latlng.lat,
			longitude: latlng.lng
		})
		.done(function(result) {
			self.base().removeClass("is-loading");
			self.updateField(result);
		});

	}, 250),

	lookup: $.debounce(function(address) {

		// self.detectButton().addClass("is-loading");
		self.base().addClass("is-loading");

		EasySocial.ajax('site/controllers/location/getLocations', {
			query: address
		})
		.done(function(locations) {

			// Store a copy of the results
			self.locations[address] = locations;

			// Suggestion locations
			self.suggest(locations);

			self.lastQueryAddress = address;
		});

	}, 250),

	suggest: function(locations) {

		var suggestions = self.suggestions();

		// Clear location suggestions
		suggestions
			.empty();

		if (locations.length < 0) {
			return;
		}

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

		self.showSuggestions();

		self.base().removeClass("is-loading");
	},

	showSuggestions: function() {

		self.focusSuggestion = true;

		self.element.find(".es-story-footer")
			.addClass("swap-zindex");

		self.story.clearMessage();

		setTimeout(function(){

			self.autocomplete().addClass("active");

			var doc = $(document),
				hideOnClick = "click.es.story.location";

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

		self.focusSuggestion = false;

		self.autocomplete().removeClass("active");

		$(document).off("click.es.story.location");

		setTimeout(function(){

			if (self.focusSuggestion) return;

			self.element.find(".es-story-footer")
				.removeClass("swap-zindex");

		}, 500);
	},

	"{suggestion} activate": function(suggestion, event) {
		if (self.osm === undefined) {
			self.initMap();
		}

		var location = suggestion.data("location");

		var lat = location.latitude,
			lng = location.longitude;

		self.navigate(lat, lng);
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

		self.hideSuggestions();
	},

	set: function(location) {
		self.currentLocation = location;
		location.fulladdress = location.name;

		self.navigate(location.latitude, location.longitude);

		// Set the address on the field
		self.textField().val(location.fulladdress);

		self.lastQueryAddress = location.address;

		// Get the caption
		EasySocial.ajax('site/views/location/getStoryCaption', {
			"address": location.fulladdress
		}).done(function(caption) {
			self.story.setMeta("location", caption);
		})

		self.base()
			.removeClass('is-loading')
			.addClass("has-location");
	},

	unset: function() {

		if (self.marker !== undefined) {
			self.osm.removeLayer(self.marker);
		}

		self.currentLocation = null;

		self.textField().val('');

		self.story.removePanelCaption("locations");

		self.story.setMeta("location", "");

		self.base().removeClass("has-location");
	},

	activatePanel: function() {

		setTimeout(function(){
			self.textField().focus();
		}, 500);
	},

	deactivatePanel: function() {

		var location = self.currentLocation;

		if (location) {
			self.set(location);
		}
	},

	detectTimer: null,

	"{detectButton} click": function() {
		self.detectButton().addClass("is-loading");
		self.initMap();
		self.osm.locate();
		self.osm.on('locationfound', function(e) {
			self.detectButton().removeClass('is-loading');
			lat = e.latitude,
			lng = e.longitude;

			self.navigate(lat, lng);
			self.lookupLatLng(e.latlng);
		});
	},

	"{removeButton} click": function() {
		self.unset();
		self.hideSuggestions();
	},

	"{meta} click": function() {
		self.story.activateMeta("location");
	},

	"{story} activateMeta": function(el, event, meta) {

		if (meta.name==="location") {
			setTimeout(function(){
				self.textField().focus();
			}, 1);
		}
	},

	"{story} save": function(event, element, save) {

		var currentLocation = self.currentLocation;

		if (!currentLocation) return;

		var task = save.addTask('saveLocation');

		self.save(task, currentLocation);
	},

	save: function(task, location) {
		task.save.data['locations_short_address'] = location.name;
		task.save.data['locations_lat'] = location.latitude;
		task.save.data['locations_lng'] = location.longitude;
		task.save.data['locations_formatted_address'] = location.fulladdress;
		task.save.data['locations_data'] = JSON.stringify(location);

		task.resolve();
	},

	"{story} clear": function() {
		self.unset();
		self.hideSuggestions();

		if (self.osm !== undefined) {
			self.osm.remove();
			delete self.osm;
		}

		self.detectButton().removeClass("t-hidden");

	}
}});

// Resolve module
module.resolve();

});

});
