EasySocial.module('site/friends/filter', function($) {

var module 	= this;

EasySocial.require()
.script('site/friends/browser')
.done(function($) {

EasySocial.Controller('Friends.Filter', {
	defaultOptions: {

		"{counters}": "[data-counter]",
		"{filter}": "[data-es-friends-filters] [data-filter-item]",
		"{browserController}": "[data-es-friends-wrapper]"
	}
}, function(self, opts) { return {

	getController: function() {
		var controller = self.browserController().controller();

		return controller;
	},

	updateFriendRequestCount: function(value) {

		curCount = parseInt(self.requestCount().text(), 10);

		if (curCount != NaN) {
			curCount = curCount + value;
			self.requestCount().text(curCount);
		}
	},

	setActiveFilter: function(item) {
		self.filter().removeClass('active');

		item.addClass('is-loading active');
	},

	"{self} onEasySocialUpdateFriendCounters": function() {

		EasySocial.ajax('site/controllers/friends/getCounters')
		.done(function(all, pending, requests, suggestions) {

			self.filter('[data-type="all"]')
				.find(self.counters.selector)
				.html(all);

			self.filter('[data-type="pending"]')
				.find(self.counters.selector)
				.html(pending);

			self.filter('[data-type="request"]')
				.find(self.counters.selector)
				.html(requests);

			self.filter('[data-type="suggest"]')
				.find(self.counters.selector)
				.html(suggestions);
		});
	},

	"{self} onEasySocialUpdateFriendListCounters": function() {
		EasySocial.ajax('site/controllers/friends/getListCounts')
		.done(function(lists) {
			$(lists).each(function(i, list) {
				self.filter('[data-type="list"][data-id="' + list.id + '"]')
					.find(self.counters.selector)
					.html(list.count);
			});
		});
	},

	"{filter} click" : function(element, event) {
		var controller = self.getController();

		if (controller === undefined) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		// Remove all active state on the filter links.
		self.setActiveFilter(element);

		var type = element.data('type');
		var anchor = element.find('> a');
		anchor.route();

		// If the type of filter is a list, we need to perform a different action
		var listId = (type == 'list') ? element.data('id') : undefined;

		controller.getFriends(type, listId, function() {
			element.removeClass('is-loading');
		});
	}
}});

module.resolve();
});
});
