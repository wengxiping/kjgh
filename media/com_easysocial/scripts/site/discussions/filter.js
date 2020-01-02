EasySocial.module('site/discussions/filter', function($){

var module = this;

EasySocial.require()
.script('site/discussions/browser')
.done(function($) {

EasySocial.Controller('Discussions.Filter', {
	defaultOptions: {
		clusterType: false,
		clusterId: false,
		"{filter}": "[data-es-discussions-filter] [data-filter-item]",
		"{discussionsController}": "[data-es-discussions][data-es-container]"
	}
}, function(self,opts,base) { return {

	init : function() {
		self.controller = self.getController();
	},

	getController: function() {
		var controller = self.discussionsController().controller();
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

		// Route the anchor links embedded
		var anchor = filter.find('> .o-tabs__link');
		anchor.route();

		var type = filter.data('filter-item');

		self.setActiveFilter(filter);

		self.controller.getDiscussions(type, function(contents) {
			filter.removeClass('is-loading');
		});
	}
}});

module.resolve();

});
});
