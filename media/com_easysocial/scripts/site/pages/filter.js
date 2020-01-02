EasySocial.module('site/pages/filter', function($) {

var module	= this;

EasySocial.require()
.script('site/pages/browser')
.done(function($) {

EasySocial.Controller('Pages.Filter', {
	defaultOptions: {
		"{filter}": "[data-es-page-filters] [data-filter-item]",
		"{browserController}": "[data-es-pages]"
	}
}, function(self, opts) { return {
	controller: null,

	init: function() {
		self.controller = self.getController();
	},

	getController: function() {
		var controller = self.browserController().controller();

		return controller;
	},

	setActiveFilter: function(filter) {
		// Set correct active state
		self.filter().removeClass('active');
		filter.addClass('active');

		filter.find('a').route();

		filter.addClass('is-loading');
	},

	"{filter} click": function(filter, event) {
		if (!self.controller) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		// Set active filter state
		self.setActiveFilter(filter);

		var type = filter.data('type');
		var options = {
			'userId': opts.userId,
			'categoryId': filter.data('id')
		};

		self.controller.getPages(type, options, function() {
			filter.removeClass('is-loading');
		});
	}

}});

module.resolve();

});

});
