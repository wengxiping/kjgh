EasySocial.module('site/groups/browser', function($) {

var module = this;

EasySocial.require()
.done(function($) {

EasySocial.Controller('Groups.Browser', {
	defaultOptions: {
		"{wrapper}": "[data-wrapper]",
		"{subWrapper}": "[data-sub-wrapper]",
		'{contents}': '[data-contents]',
		"{list}": "[data-list]",

		// Fetching location wrapper
		"{location}": "[data-fetching-location]",
		"{locationMessage}": "[data-detecting-location-message]",

		"{ordering}": "[data-sorting]",
		'{radius}': '[data-radius]',
	}
}, function(self, opts) { return {

	clearContents: function(showLoading) {
		var showLoading = showLoading === undefined ? true : showLoading;

		self.element.removeClass('is-detecting-location');
		self.contents().empty();

		if (showLoading) {
			self.wrapper().addClass('is-loading');
		}
	},

	setContents: function(contents) {

		// Remove loading indicators
		self.wrapper().removeClass('is-loading');

		self.contents().html(contents);
	},

	updatingListing: function() {
		self.list().empty();
		self.subWrapper().addClass('is-loading');
	},

	updateListing: function(html) {
		self.subWrapper().removeClass('is-loading');
		self.list().html(html);
	},

	setLocation: function(latitude, longitude) {
		opts.latitude = latitude;
		opts.longitude = longitude;
	},

	setUserId: function(userId) {
		opts.userId = userId;
	},

	setFilter: function(filter) {
		opts.filter = filter;
	},

	setCategoryId: function(categoryId) {
		opts.categoryid = categoryId;
	},

	getItems: function(isOrdering, callback) {

		var options = {
			"userId": opts.userId,
			"filter": opts.filter,
			"categoryid": opts.categoryid,
			"ordering": opts.ordering,
			"sort": isOrdering ? 1 : 0,
		};

		if (options.filter == 'nearby') {
			options["latitude"] = opts.latitude;
			options["longitude"] = opts.longitude;
			options["distance"] = opts.distance;
		}

		EasySocial.ajax('site/controllers/groups/filter', options)
		.done(function(contents, distanceUrl) {

			if ($.isFunction(callback)) {
				callback.call(this, contents, distanceUrl);
			}

			if (isOrdering) {
				self.updateListing(contents);
				return;
			}

			self.setContents(contents);

			$('body').trigger('afterUpdatingContents', [contents]);
		});
	},

	"{ordering} click" : function(ordering, event) {

		// Get the sort type
		var type = ordering.data('type');
		var categoryId = ordering.data('id');

		// Route the item so that we can update the url
		ordering.route();

		// Add the active state on the current element.
		opts.ordering = type;

		self.ordering().removeClass('active');
		ordering.addClass('active');

		self.updatingListing();
		self.getItems(true);
	},

	'{radius} click': function(dropdown, event) {

		// Get the distance
		opts.distance = dropdown.data('radius');

		self.updatingListing();

		self.getItems(true, function(contents, distanceUrl) {
			// Update the current URL now since the distance has changed
			History.pushState({state:1}, document.title, distanceUrl);
		});
	}
}});

module.resolve();

});
});
