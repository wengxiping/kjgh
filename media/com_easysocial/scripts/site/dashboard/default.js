EasySocial.module('site/dashboard/default' , function($){

var module = this;

EasySocial.require()
.library('history')
.script('site/stream/filter')
.done(function($){

EasySocial.Controller('Dashboard', {
	"defaultOptions": {
		"title": null,

		// Wrapper and content
		"{wrapper}": "[data-wrapper]",
		"{contents}": "[data-contents]",

		// Filter dropdown
		"{activeFilterText}": "[data-active-filter-text]",
		'{activeFilterButton}': "[data-active-filter-button]",
		"{filter}": "[data-filter-item]",
		"{filterWrapper}": "[data-filter-wrapper]",

		// Post type filters
		"{postTypeFilterWrapper}": "[data-filter-post-type-wrapper]",
		"{postTypeFilter}": "[data-filter-post-type]",
		"{postTypeFilterLabel}": "[data-filter-post-type-label]",

		// Hidden inputs to manipulate the states
		"{filterInput}": "[data-stream-filter]",

		// Custom filters
		"{createFilter}": "[data-filter-create]",
		"{editFilter}": "[data-edit-filter]",
		"{saveHashTag}": "[data-hashtag-filter-save]"
	}
}, function(self, opts) { return{

	init: function() {
		console.log('hello');
	},

	resetFilterCounters: function(filterItem) {
		// Clear the new feed notification counter.
		var counter = filterItem.find('[data-counter]');

		// Update the counter to 0
		counter.html("0");

		// Clear new feed counter
		filterItem.removeClass('has-notice');
	},

	setActiveFilter: function(filter) {
		// Find the wrapper
		var wrapper = filter.parents(self.filterWrapper.selector);
		var filters = wrapper.find(self.filter.selector);
		var type = filter.data('type');

		self.filterInput().val(type);

		filters.removeClass('active');
		filter.addClass('active');

		var title = filter.find('[data-filter-text]').html();
		var activeFilterText = wrapper.find(self.activeFilterText.selector);

		activeFilterText.html(title);
		wrapper.removeClass('open');
	},

	updateStream: function(filter, filterId, postTypes) {

		// Used only for custom filters
		var filterId = filterId != undefined ? filterId : '';

		// Used in combination with filter
		var postType = postType != undefined ? postType : '';

		// Notify the dashboard that it's starting to fetch the contents.
		self.updatingContents();

		// Remove empty state
		self.wrapper()
			.removeClass('is-empty');

		EasySocial.ajax('site/controllers/dashboard/getStream', {
			"type": filter,
			"postTypes": postTypes,
			"id": filterId,
			"view": 'dashboard',

		}).done(function(contents, count) {

			if (count == 0) {
				self.wrapper().addClass('is-empty');
			}

			// Trigger change for the stream
			self.trigger('onStreamUpdate', [filter]);

			// Trigger sidebar toggle for responsive view.
			self.trigger('onEasySocialFilterClick');

			window.streamFilter = filter;

			// Update the contents of the dashboard area
			self.updateContents(contents);

			// 3PD FIX: Kunena [text] replacement
			try {
				MathJax && MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
			} catch(err) {};

		}).fail(function(message) {
			return message;
		}).always(function() {

			// Give a tiny buffer to prevent call stack request from maxing out. #1733
			setTimeout(function() {
				self.clicked = false;
			}, 10);

		});
	},

	updatingContents: function() {
		self.contents().empty();
		self.wrapper().addClass('is-loading');
	},

	updateContents: function(contents) {
		self.wrapper().removeClass("is-loading");

		$('body').trigger('beforeUpdatingContents');

		// Hide the content first.
		$.buildHTML(contents)
			.appendTo(self.contents());

		$('body').trigger('afterUpdatingContents');
	},

	// Allows caller to remove all checked post types
	resetPostTypes: function() {
		self.postTypeFilter().removeAttr('checked');
	},

	// Prevent closing of dropdown
	"{postTypeFilterWrapper} click": function(element, event) {
		event.preventDefault();
		event.stopPropagation();
	},

	// Since we have prevented the closing of dropdown, we need to manually stop propagation here
	"{postTypeFilterLabel} click": function(element, event) {
		event.stopPropagation();
	},

	// Since we have prevented the closing of dropdown, we need to manually stop propagation here
	"{postTypeFilter} click": function(element, event) {
		event.stopPropagation();
	},

	"{postTypeFilter} change": function(element, event) {

		var active = self.getActiveFilter();
		var filter = active.data('type');
		var id = active.data('id');

		// Whenever there is a change of post type filter, we just recompute the value
		var postTypes = [];

		self.postTypeFilter(':checked').map(function() {
			postTypes.push($(this).val());
		});

		// Get the current filter on the page
		self.updateStream(filter, id, postTypes);
	},

	"{filter} click": function(filter, event) {

		// Prevent clicking any items more than once
		if (self.clicked) {
			return false;
		}

		self.resetPostTypes();
		self.clicked = true;

		// Prevent event from bubbling up
		event.preventDefault();
		event.stopPropagation();

		// Get the attributes of the item
		var type = filter.data('type');
		var id = filter.data('id');

		// Route the anchor links embedded
		var anchor = filter.find('> a');

		anchor.attr('title', opts.title);
		anchor.route();

		// Set the active filter
		self.setActiveFilter(filter);

		self.updateStream(type, id);
	},

	"{editFilter} click": function(element, event) {

		// Get the filter attributes
		var id = element.data('id');
		var type = element.data('type');

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/stream/getFilterFormDialog', {
				"type": type,
				"id": id
			})
		});
	},

	"{createFilter} click": function(element, event) {
		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/stream/getFilterFormDialog', {"type": "user"})
		});
	},

	"{saveHashTag} click": function(el) {
		var hashtag = el.data('tag');

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/stream/getFilterFormDialog', {
				"type": "user",
				"hashtag": hashtag
			})
		});
	}
}});

module.resolve();

});

});
