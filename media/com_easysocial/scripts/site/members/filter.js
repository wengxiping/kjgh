EasySocial.module('site/members/filter', function($){

var module = this;

EasySocial.require()
.script('site/groups/browser', 'site/events/guests', 'site/pages/followers')
.done(function($) {

EasySocial.Controller('Members.Filter', {
	defaultOptions: {
		"{filterWrapper}": "[data-es-member-filter]",
		"{filter}": "[data-es-member-filter] [data-filter]",
		"{groupController}": "[data-es-group-members][data-es-container]",
		"{pageController}": "[data-es-page-followers][data-es-container]",
		"{eventController}": "[data-es-event-guests][data-es-container]"
	}
}, function(self,opts,base) { return {

	init : function() {
		self.clusterId = self.filterWrapper().data('clusterId');
		self.clusterType = self.filterWrapper().data('clusterType');

		self.controller = self.getController();
	},

	getController: function() {
		var controller = self[self.clusterType + "Controller"]().controller();
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

		var type = filter.data('type');

		self.setActiveFilter(filter);

		self.controller.getItems(type, self.clusterId, function(contents) {
			filter.removeClass('is-loading');
		});
	}
}});

module.resolve();

});
});
