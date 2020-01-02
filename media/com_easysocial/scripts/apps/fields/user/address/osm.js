EasySocial.module('apps/fields/user/address/osm', function($) {
var module = this;

// Create search template first
$.template('easysocial/maps.suggestion', '<div class="es-location-suggestion" data-location-suggestion><span class="formatted_address">[%= location.formatted_address %]</span></div>');

EasySocial
.require()
.library('leaflet', 'placeholder', 'image', 'leaflet-providers')
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

EasySocial.Controller('Field.Address.Osm', {
	defaultOptions: {
		required: false,
		mapElementId: 'map',
		staticMap: false,

		'{field}': '[data-field-address]',
		'{base}': '[data-location-base]',

		'{map}': '[data-location-map]',
		'{mapImage}': '[data-location-map-image]',

		'{detectButton}': '[data-location-detect]',
		'{removeButton}': '[data-location-remove]',

		'{form}': '[data-location-form]',
		'{textbox}': '[data-location-textbox]',
		'{textField}': '[data-location-textfield]',

		'{autocomplete}': '[data-location-autocomplete]',
		'{suggestions}': '[data-location-suggestions]',
		'{suggestion}': '[data-location-suggestion]',

		'{source}': '[data-location-source]',

		view: {
			suggestion: 'maps.suggestion'
		},

		marker: {},
	}
}, function(self, opts, base) { return {
	init: function() {

		if (self.options.mapElementId) {
			self.mapElementId = self.options.mapElementId;
		}

		var data = self.field().htmlData();

		opts.error = data.error || {};

		// Only show auto-detect button if the browser supports geolocation
		if (navigator.geolocation && window.es.isHttps) {
			self.base().addClass("is-detectable");
			self.detectButton().show();
		}

		self.textField().removeAttr("disabled");

		if (!$.isEmpty(self.source().val())) {
			var data = JSON.parse(self.source().val());

			if (data.latitude && data.longitude) {
				lat = data.latitude;
				lng = data.longitude;

				self.initMap();

				self.navigate(lat, lng);

				self.base().addClass("has-location");
			}
		}
	},

	initMap: function() {

		if (opts.staticMap) {
			return;
		}

		if (self.osm !== undefined) {
			return;
		}

		self.osm = L.map(self.mapElementId, {
			zoom: 12
		});

		self.osm.fitWorld();

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

	marker: {},

	updateField: function(result) {

		// Fill in the field with address
		self.textField().val(result[0].formatted_address);

		self.base().addClass("has-location");

		// Set the source here
		self.result = result[0];
		var data = self.getResult('source');
		self.source().val(JSON.stringify(data));
	},


	"{window} resize": $.debounce(function() {

		var data = JSON.parse(self.source().val());

		if (!data.latitude || !data.longitude) {
			return;
		}

		var mapImage = self.mapImage();

		if (mapImage.data("width") !== mapImage.width()) {
			self.navigate(data.latitude, data.longitude);
		}

	}, 250),

	'{self} onShow': function() {

		var data = JSON.parse(self.source().val());

		if (!data.latitude || !data.longitude) {
			return;
		}

		var mapImage = self.mapImage();

		if (mapImage.data("width") !== mapImage.width()) {
			self.navigate(data.latitude, data.longitude);
		}
	},

	navigate: function(lat, lng) {
		if (opts.staticMap) {
			self.navigateStatic(lat, lng);
		} else {
			self.navigateDynamic(lat, lng);
		}
	},

	navigateStatic: function(lat, lng) {
		self.field().css({
			"max-width": "none"
		});

		var url = "//maps.wikimedia.org/img/osm-intl,13," + lat + "," + lng + ",600x300.png";

		var mapImage = self.mapImage();

		// When map is loaded, fade in.
		$.Image.get(url)
			.done(function(){
				mapImage.css({
					"backgroundImage": $.cssUrl(url),
					"backgroundSize": "cover",
					"backgroundPosition": "center center"
				});
				self.base().addClass("has-location");
			})
			.always(function(){
				self.base().removeClass("is-loading");
			});
		},

	navigateDynamic: function(lat, lng) {
		self.detectButton().addClass("t-hidden");

		self.field().css({
			"max-width": "none"
		});

		var latlng = {
					lat: parseFloat(lat),
					lng: parseFloat(lng)
				}
		self.osm.removeLayer(self.marker);

		self.osm.flyTo(latlng, 10, {
			"duration": 3
		});

		self.marker = L.marker(latlng).addTo(self.osm);
	},

	locations: {},

	lastQueryAddress: null,

	results: [],

	result: null,

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

				if (address==="") {
					self.base().removeClass("has-location");
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

		self.detectButton().addClass("is-loading");
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

		if (locations.length < 0) return;

		self.results = locations;

		$.each(locations, function(i, location){

			location.formatted_address = location.formatted_address;
			// Create suggestion and append to list
			self.view.suggestion({
					location: location
				})
				.data("location", location)
				.appendTo(suggestions);
		});

		self.showSuggestions();

		self.base().removeClass("is-loading");
	},

	showSuggestions: function() {

		self.focusSuggestion = true;

		self.element.find(".es-story-footer")
			.addClass("swap-zindex");

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

		self.initMap();

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

		self.initMap();

		var location = suggestion.data("location");

		self.set(location);

		self.hideSuggestions();

		self.validateInput();
	},

	set: function(location) {
		self.currentLocation = location;

		var lat = location.latitude,
			lng = location.longitude;

		var address = location.formatted_address;

		self.textField().val(address);

		self.lastQueryAddress = address;

		self.base().addClass("has-location");

		// Set the source here
		self.result = location;
		var data = self.getResult('source');
		self.source().val(JSON.stringify(data));
	},

	unset: function() {

		self.osm.removeLayer(self.marker);

		self.currentLocation = null;

		self.textField().val('');

		self.base().removeClass("has-location");

		self.source().val('');
	},

	detectTimer: null,

	"{detectButton} click": function() {

		var textbox = self.textbox();

		self.detectButton().addClass("is-loading");

		clearTimeout(self.detectTimer);

		self.detectTimer = setTimeout(function() {
			self.detectButton().removeClass("is-loading");

			navigator.geolocation.getCurrentPosition(

				// If successful
				function(position) {
					var coords = position.coords;

					lat = coords.latitude,
					lng = coords.longitude;

					self.initMap();

					self.navigate(lat, lng);
					self.lookupLatLng({lat: lat, lng: lng});
				}
			);
		}, 2000);
	},

	raiseError: function(message) {
		self.trigger('error', [message]);
	},

	clearError: function() {
		self.trigger('clear');
	},

	validateInput : function() {
		self.clearError();

		if ($.isEmpty(self.source().val()) && opts.required) {
			self.raiseError(opts.error.maps);
			return false;
		}

		var value = self.source().val();

		if (value) {
			var data = JSON.parse(value);

			if ((!data.latitude || !data.longitude) && opts.required) {
				self.raiseError(opts.error.maps);
				return false;
			}
		}

		return true;
	},

	"{self} onSubmit" : function(el, event, register) {
		register.push(self.validateInput());
	},

	"{removeButton} click": function() {
		self.unset();
		self.hideSuggestions();
		self.validateInput();
	},

	getResult: function(type) {
		if (!self.result) {
			if (self.results.length === 0) {
				return false;
			}

			self.result = self.results[0];
		}

		var r = self.result;

		if (type === undefined) {
			return r;
		}

		switch(type) {
			case 'coords':
				return {
					lat: r.latitude,
					lng: r.longitude
				}
			break;

			case 'lat':
			case 'latitude':
				return r.latitude;
			break;

			case 'lng':
			case 'longitude':
				return r.longitude;
			break;

			case 'address':
				return r.formatted_address;
			break;

			// case 'viewport':
			// 	return r.geometry.viewport;
			// break;

			// case 'bounds':
			// 	return r.geometry.bounds || r.geometry.viewport;
			// break;

			case 'source':
				var components = {};

				$.each(r.address, function(index, component) {
					components[index] = component;
				});

				var mapping = {
					'address1': ['mall', 'route', 'building', 'public_building'],
					'address2': ['road', 'neighborhood', 'premise', 'subpremise'],
					'city': ['city', 'suburb'],
					'state': 'state',
					'zip': 'postcode',
					'country': 'country'
				};

				// Based on the mapping we build the legacy data
				var legacy = {};

				$.each(mapping, function(key, value) {

					// Init with empty data
					legacy[key] = '';

					if ($.isArray(value)) {
						$.each(value, function(i, v) {

							// Search if components[v] exists
							if (components[v] !== undefined) {

								// Use it if it exists
								legacy[key] = components[v];

								// Break out and ignore other possible keys
								return false;
							} else {

								// Continue finding
								return true;
							}
						});

						// Continue on to the next key
						return true;
					}

					if (components[value] !== undefined) {
						legacy[key] = components[value];
					}
				});

				var data = $.extend(legacy, {
					components: components,
					address: r.formatted_address,
					latitude: r.latitude,
					longitude: r.longitude
				});

				return data;
			break;
		}
	}
}});

module.resolve();
});
});
