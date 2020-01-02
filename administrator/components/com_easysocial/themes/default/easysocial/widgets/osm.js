
EasySocial.require()
.library('leaflet', 'leaflet-providers')
.done(function($) {

	var renderMap = function() {

		EasySocial.ajax('admin/controllers/easysocial/getCountries', {
		}).done(function(countries, content) {

			var newContent = $(content);

			// Render the map first
			self.osm = L.map('user-locations', {
				zoom: 15
			});

			self.osm.fitWorld();

			L.tileLayer.provider('Wikimedia').addTo(self.osm);

			var latlng = {
						lat: parseFloat(-12.043333),
						lng: parseFloat(-77.028333)
					}

			$.each(countries, function(index, value) {
				EasySocial.ajax('admin/controllers/easysocial/getLocations', {
					query: value
				})
				.done(function(locations) {
					var country = locations[0].formatted_address;
					var tableRow = $('[data-country="' + country + '"]');
					var count = tableRow.find('[data-counter]').text();

					$(newContent)
						.find('[data-stat-country="' + value + '"]')
						.html(country);

					var latlng = {
								lat: parseFloat(locations[0].latitude),
								lng: parseFloat(locations[0].longitude)
							}

					L.marker(latlng).addTo(self.osm);
				});

			});

			$('[data-map-table-wrapper]').html(newContent);

		});

	};
	$('[data-form-tabs]').on('click', function() {
		var tab = $(this);
		var type = tab.data('item');

		if (type == 'world-map') {
			renderMap();
		}
	})
});
