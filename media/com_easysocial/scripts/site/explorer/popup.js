EasySocial.module('site/explorer/popup', function($) {

var module = this;

$.template('explorer/popup', '<div id="es" class="es es-explorer-popup" data-explorer-popup><div class="es-popup-viewport" data-popup-viewport></div><div class="o-loader"></div></div>');

$.Controller("Explorer/Popup", {
	defaultOptions: {

		view: {
			popup: "explorer/popup"
		},

		"{popup}"   : "[data-explorer-popup]",
		"{viewport}": "[data-popup-viewport]",
		"{explorer}": "[data-explorer-popup] .fd-explorer",
		"{closeButton}": "[data-close]"
	}
}, function(self, opts, base) { return {

	init: function() {

	},

	"{window} resize": $.debounce(function() {
	}, 100),

	show: function() {

		var popup;
		var node = self.popup.node;

		// Create node if not exists
		if (!node) {
			popup = self.view.popup();
			node  = self.popup.node = popup[0];
		}

		// Append node if detached
		if (!$.contains(base, node)) {
			popup = $(node).appendTo(base);
		}

		if (!popup.is(":visible")) {
			popup.show().trigger("show");
		}

		if (window.es.mobile) {
			popup.addClass('is-mobile');
		}
	},

	hide: function() {

		self.popup()
			.hide()
			.trigger("hide")
			.detach();
	},

	// options: uid, type, url
	open: function(options) {

		// Show the popup dialog
		self.show();

		var task = $.Deferred();

		var existingExplorer = self.explorer();

		if (existingExplorer.length > 0 &&
			existingExplorer.data("uid")===options.uid &&
			existingExplorer.data("type")===options.type) {
			return task.resolve(existingExplorer.explorer("controller"), self);
		}

		EasySocial.ajax("site/views/explorer/browser", options)
			.done(function(html){

				var browser = $.buildHTML(html);

				self.viewport()
					.empty()
					.append(browser);

				var explorer = browser.filter("[data-es-explorer]").explorer("controller");

				task.resolve(explorer, self);
			})
			.fail(function(){
				task.reject();
			});

		return task;
	},

	"{self} click": function(el, event) {

		if (event.target===self.popup()[0]) {
			self.hide();
		}
	},

	"{closeButton} click": function() {
		self.hide();
	}

}});

var instance = EasySocial.explorer = $("body").addController("Explorer/Popup");

module.resolve(instance);

});
