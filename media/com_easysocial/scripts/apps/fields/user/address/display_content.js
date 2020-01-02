EasySocial.module('apps/fields/user/address/display_content', function($) {

var module = this;

EasySocial
.require()
.library('gmaps', 'leaflet', 'leaflet-providers')
.done(function() {

EasySocial.Controller('Field.Address.Display', {
	defaultOptions: {
		latitude: null,
		longitude: null,
		ratio: 1,
		mapElementId: 'map',
		'{base}': '[data-location-base]',
		'{map}': '[data-location-map]',
		'{mapImage}': '[data-location-map-image]'
	}
}, function(self) { return {

	init: function() {

		if (self.options.mapElementId) {
			self.mapElementId = self.options.mapElementId;
		}

		var map = self.map();
		self.options.latitude = map.data('latitude');
		self.options.longitude = map.data('longitude');
		self.options.provider = map.data('location-provider');

		self.setLayout();
	},

	'{window} resize': $.debounce(function() {
		self.setLayout();
	}, 250),

	navigate: function(lat, lng) {
		if (!self.options.staticMap && self.options.provider == 'osm') {
			self.navigateDynamic(lat, lng);
			self.base().addClass("has-location");
			self.base().removeClass("is-loading");
			return;
		}

		var mapImage = self.mapImage(),
			width = Math.floor(mapImage.width()),
			height = Math.floor(mapImage.height());

		if (self.options.provider == 'osm') {
			var url = "//maps.wikimedia.org/img/osm-intl,15," + lat + "," + lng + ",600x300.png";
		} else {
			var url = $.GMaps.staticMapURL({
					size: [1280, 1280],
					lat: lat,
					lng: lng,
					sensor: true,
					scale: 2,
					markers: [
						{lat: lat, lng: lng}
					]
				});
		}

		var url = url.replace(/http\:|https\:/, '');

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
		self.osm = L.map(self.mapElementId, {
			zoom: 12
		})

		self.osm.fitWorld();

		L.tileLayer.provider('Wikimedia').addTo(self.osm);

		var latlng = {
			lat: parseFloat(lat),
			lng: parseFloat(lng)
		}

		self.osm.flyTo(latlng, 13, {
			"duration": 3
		});

		marker = L.marker(latlng).addTo(self.osm);
	},

	setLayout: function() {
		setTimeout(function() {
			if (self.options.latitude && self.options.longitude) {
				self.navigate(self.options.latitude, self.options.longitude);
				// self.navigateDynamic(self.options.latitude, self.options.longitude);
			}
		}, 1);
	},

	'{self} onShow': function() {
		self.setLayout();
	}
}});

module.resolve();
});
});
