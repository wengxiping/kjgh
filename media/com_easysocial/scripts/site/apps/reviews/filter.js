EasySocial.module('site/apps/reviews/filter', function($){

var module = this;

EasySocial.require()
.script('site/apps/reviews/reviews')
.done(function($) {

EasySocial.Controller('Apps.Reviews.Filter', {
	defaultOptions: {
		"{filterWrapper}": "[data-es-reviews-filter]",
		"{filter}": "[data-es-reviews-filter] [data-review-filter]",
		"{filterController}": "[data-es-structure] [data-es-reviews]"
	}
}, function(self,opts,base) { return {

	init : function() {
		self.controller = self.getController();
	},

	getController: function() {
		var controller = self.filterController().controller();
		return controller;
	},

	setActiveFilter: function(filter) {

		var activeClass = (window.es.mobile || window.es.tablet || window.es.ios) ? 'is-active' : 'active';

		self.filter().removeClass(activeClass);

		filter.addClass(activeClass);
	},

	"{filter} click": function(filter, event) {

		if (self.controller === undefined) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		var type = filter.data('review-filter');

		self.setActiveFilter(filter);

		self.controller.getItems(type, self.clusterId, function(contents, empty) {
			filter.removeClass('is-loading');
		});
	}
}});

module.resolve();

});
});
