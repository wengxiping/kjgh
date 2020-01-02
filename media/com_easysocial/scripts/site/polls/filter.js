EasySocial.module('site/polls/filter', function($){

var module = this;

EasySocial.require()
.script('site/polls/browser')
.done(function($) {

EasySocial.Controller('Polls.Filter', {
	defaultOptions: {
		clusterType: false,
		clusterId: false,
		"{filter}": "[data-es-polls-filter] [data-filter-item]",
		"{pollsController}": "[data-es-polls][data-es-container]"
	}
}, function(self,opts,base) { return {

	init : function() {
		self.controller = self.getController();
	},

	getController: function() {
		var controller = self.pollsController().controller();
		return controller;
	},

	setActiveFilter: function(filter) {

		var activeClass = (window.es.mobile || window.es.tablet || window.es.ios) ? 'is-active' : 'active';

		self.filter().removeClass(activeClass);

		filter.addClass(activeClass)
 			  .addClass('is-loading');
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

		self.controller.getPolls(type, function(contents) {
			filter.removeClass('is-loading');
		});
	}
}});

module.resolve();

});
});
