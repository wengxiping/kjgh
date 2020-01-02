EasySocial.module('site/stream/ads', function(){

var module = this;

EasySocial.require()
.done(function($) {

EasySocial.Controller('Ads', {

	defaultOptions: {
		"{adsLink}": "[data-ads-link]"
	}
}, function(self, opts) { return {

	init : function() {
		self.on("scroll.stream", window, $._.debounce(function() {

			if (self.element.find('[data-contents]').visible()) {
				// Get previous stream id
				var prevStreamId = self.element.prev().data('id'),
				adStreamId = self.element.data('id');

				var sid = prevStreamId + '-' + adStreamId;

				if (window.adseen === undefined) {
					window.adseen = [];
				}

				if ($.inArray(sid, window.adseen) === -1) {
					EasySocial.ajax('site/controllers/ads/view', {
						"id" : self.element.data('id')
					}).done(function() {
						window.adseen.push(sid);
					});
				}
			}
		}, 250));
	},

	"{adsLink} click": function(el, event) {
		event.preventDefault();

		var href = self.element.data('link');

		EasySocial.ajax('site/controllers/ads/click', {
			"id" : self.element.data('id')
		});

		window.open(href);
	}

}});

module.resolve();
});

});
