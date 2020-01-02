EasySocial.module('site/locations/popbox', function($){

	EasySocial.module("locations/popbox", function($){

		this.resolve(function(popbox){

			var button = popbox.button,
				lat = button.data("lat"),
				lng = button.data("lng"),
				language = window.es.locationLanguage || 'en',
				provider = button.data("location-provider");

				if (provider == 'osm') {
					link = "//www.openstreetmap.org/#map=16/" + lat + "/" + lng;
					url = "//maps.wikimedia.org/img/osm-intl,13," + lat + "," + lng + ",600x300.png";
				} else {
					apiKey = window.es.gmapsApiKey;
					link = "//maps.google.com/?q=" + lat + "," + lng,
					url = "//maps.googleapis.com/maps/api/staticmap?key=" + apiKey + "&size=400x200&sensor=true&zoom=15&center=" + lat + "," + lng + "&markers=" + lat + "," + lng + "&language=" + language;
				}
			return {
				id: "es",
				component: "",
				type: "location",
				content: '<a href="' + link + '" target="_blank"><img src="' + url + '" width="400" height="200" /></a>'
			}
		});

	});

	this.resolve();
});
