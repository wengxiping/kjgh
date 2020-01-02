EasySocial.module('site/pages/browser', function($) {

var module	= this;

EasySocial.Controller('Pages.Browser', {
	defaultOptions: {

		// Result
		"{wrapper}": "[data-wrapper]",
		"{result}": "[data-result]",
		"{list}": "[data-list]",
		"{header}": "[data-header]",

		// Sorting
		"{sortItem}": "[data-sorting]",
		"{items}": "[data-pages-item]",
		"{featured}": "[data-pages-featured-item]",
		"{listContents}": "[data-es-pages-list]"
	}
}, function(self, opts) { return {

	setActiveSort: function(sortItem) {

		// Set the correct active state
		self.sortItem().removeClass('active');
		sortItem.addClass('active');

		// Only remove the contents of the page listings
		self.list().empty();

		// Set the loading indicator of the result area
		self.result().addClass('is-loading')
	},

	getPages: function(filterType, options, callback) {

		// Remove all result
		self.result().empty();

		// Set loading indicator on wrapper
		self.wrapper().addClass('is-loading');

		options = $.extend({
						"filter": filterType
					}, options);

		EasySocial.ajax('site/controllers/pages/filter', options)
		.done(function(contents) {

			if (typeof(callback) == 'function') {
				callback.apply(contents);
			}

			// Remove loading indicators
			self.wrapper().removeClass('is-loading');

			// Update the contents
			self.wrapper().html(contents);

			$('body').trigger('afterUpdatingContents', [contents]);

			// trigger sidebar toggle for responsive view.
			self.trigger('onEasySocialFilterClick');

		});
	},

	"{sortItem} click" : function(sortItem, event) {

		// Get the sort type
		var type = sortItem.data('type');
		var filter = sortItem.data('filter');
		var categoryId = sortItem.data('id');

		// Route the item so that we can update the url
		sortItem.route();

		// Add the active state on the current element.
		self.setActiveSort(sortItem);

		// Render the ajax to load the contents.
		EasySocial.ajax('site/controllers/pages/filter', {
			"ordering": type,
			"filter": filter,
			"categoryId": categoryId
		}).done(function(contents) {

			self.result().removeClass('is-loading');

			self.list().html(contents);
		});
	}

}});

module.resolve();
});
