EasySocial.module('site/system/keepalive', function($){

var module = this;

EasySocial.Controller('System.KeepAlive', {
	defaultOptions: {
		"isHidden" : false,
		"hidden":null,
		"visibilityChange":null,
	}
}, function(self, opts) { return {

	init: function() {
		self.execute();
	},

	execute: function() {

		if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support 
			self.hidden = "hidden";
			self.visibilityChange = "visibilitychange";
		} else if (typeof document.msHidden !== "undefined") {
			self.hidden = "msHidden";
			self.visibilityChange = "msvisibilitychange";
		} else if (typeof document.webkitHidden !== "undefined") {
			self.hidden = "webkitHidden";
			self.visibilityChange = "webkitvisibilitychange";
		}

		document.addEventListener(self.visibilityChange, function(e) {

			var hidden = self.hidden;

			if (document[hidden]) {
				self.isHidden = true;
			}

			if (self.isHidden && !document[hidden]) {

				var keepaliveOptions = Joomla.getOptions('system.keepalive'),
					keepaliveUri = keepaliveOptions && keepaliveOptions.uri ? keepaliveOptions.uri.replace(/&amp;/g, '&') : '',
					systemPaths = Joomla.getOptions('system.paths');

				// Fallback in case no keepalive uri was found.
				if (keepaliveUri === '') {
					keepaliveUri = (systemPaths ? systemPaths.root + '/index.php' : window.location.pathname) + '?option=com_ajax&format=json';
				}

				Joomla.request({
					url: keepaliveUri,
					onSuccess: function(response, xhr) {
						var renewUri = (systemPaths ? systemPaths.root + '/index.php' : window.location.pathname) + '/index.php?option=com_easysocial&renewToken=true';

						Joomla.request({
							url: renewUri,
							onSuccess: function(response, xhr) {
								// #3257
								// we do not overwrite the window.es.token so that we stil keep track the
								// old token for later comparison in plupload.
								// see file 'media/com_easysocial/scripts/vendors/plupload.js' at line line 1152 on BeforeUpload bind.

								// update component.js session token value
								EasySocial.token.value = response;
							},
							onError: function(xhr) {
								// Do nothing
							}
						});
					},
					onError: function(xhr) {
						// Do nothing
					}
				});
			}

		}, false);
	}

}});

module.resolve();
});
