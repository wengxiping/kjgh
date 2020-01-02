EasySocial.module('site/users/default', function($){

var module = this;

EasySocial.Controller('Users', {
	defaultOptions: {

		sort: '',

		// Sorting
		"{sort}": "[data-sort]",

		// Contents and result
		"{contents}": "[data-contents]",
		"{wrapper}": "[data-wrapper]",
		"{result}": "[data-es-users-result]",
		"{header}": "[data-header]",

		// Sidebar filters
		"{filterItem}": "[data-filter-item]"
	}
}, function(self, opts) { return {

	init : function() {
		opts.sort = self.sort('.active').data('type');
	},

	setActiveFilter: function(filter) {
		// Remove all filter item's active class
		self.filterItem().removeClass('active');

		// Add active state to itself
		filter.addClass('active');
	},

	updateContents: function(contents) {
		self.result().removeClass('is-loading');

		self.result().html(contents);
	},

	getActiveFilter: function() {
		return self.filterItem('.active');
	},

	filter: function(sortRequest) {

		// Determines the filter type
		var filter = self.getActiveFilter();

		// Retrieve filter type from the sidebar e.g. users, profiles, search
		var type = filter.data('type');

		// Retrieve filter type data id e.g. all, online, withphotos, and other id (int)
		var id = filter.data('id');

		// If there unable to retrieve the valid type and id, mean the sidebar module already disabled on the site.
		// This part handling for the desktop view
		if (!type && !id) {

			// data filter attribute name return 'all' mean under 'users' type
			if (opts.filter == 'all'
				|| opts.filter == 'friends'
				|| opts.filter == 'followers'
				|| opts.filter == 'photos'
				|| opts.filter == 'online'
				|| opts.filter == 'verified'
				|| opts.filter == 'blocked') {

				var type = 'users';
				var id = opts.filter;
			}

			// Currently only these 2 filter type contain the ids
			if (opts.filter == 'profiletype') {
				var type = 'profiles';
				var id = opts.filterid;
			}

			if (opts.filter == 'search') {
				var type = opts.filter;
				var id = opts.filterid;
			}
		}

		// Add loading indicator
		self.contents().addClass('is-loading');

		if (sortRequest) {
			self.result().empty();
		} else {
			self.wrapper().empty();
		}

		EasySocial.ajax('site/controllers/users/filter', {
			"type": type,
			"id": id,
			"filter": id,
			"sorting": opts.sort,
			"pagination": 1,
			"sortRequest": sortRequest ? 1 : 0
		}).done(function(output) {

			self.contents().removeClass('is-loading');

			if (sortRequest) {
				self.result().html(output);
			} else {
				self.wrapper().html(output);
			}

			$('body').trigger('afterUpdatingContents');

		}).always(function() {
			// Remove loading state
			filter.removeClass('is-loading');
		});
	},

	"{filterItem} click": function(filter, event) {
		// Prevent default
		event.stopPropagation();
		event.preventDefault();

		// Set active filter
		self.setActiveFilter(filter);

		// Route the url
		var anchor = filter.find('> a');
		anchor.route();

		// Add loading state to the filter link
		filter.addClass('is-loading');


		if (filter.data('type') != 'users') {
			opts.sort = '';
		}

		self.filter(false);

        // trigger sidebar toggle for responsive view.
        self.trigger('onEasySocialFilterClick');
	},

	"{sort} click" : function(link, event) {

		// event.preventDefault();
		// event.stopPropagation();

		// Set active class
		self.sort().removeClass('active');
		link.addClass('active');

		// Route the link
		link.route();

		// Retrieve current sorting type from the dropdown
		var sort = link.data('type');

		// Assign to this sort option for this sorting type
		opts.sort = link.data('type');

		// Retrieve current filter data attribute name from the sorting dropdown
		opts.filter = link.data('filter');

		// Retrieve the filter type data id e.g. search and profiles
		opts.filterid = link.data('filterid');

		self.filter(true);
	}
}});

module.resolve();
});
