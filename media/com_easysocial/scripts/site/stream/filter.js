EasySocial.module('site/stream/filter', function($){

var module = this;

EasySocial.require()
.script('site/vendors/puller')
.library('history')
.done(function($){

EasySocial.Controller('Stream.Filter', {
	"defaultOptions": {
		"title": null,
		"type": null,

		// If this is a cluster, it should be the cluster id
		"uid": null,
		"ajaxNamespace": "",
		"ajaxOptions": {},
		"isMobile": false,

		// Requirement to render the content as HTML with ajax
		"ajaxControllers": "",
		"ajaxMethod": "",

		// Wrapper and content
		"{wrapper}": "[data-wrapper]",
		"{contents}": "[data-contents]",

		// Filter dropdown
		"{activeFilterText}": "[data-active-filter-text]",
		'{activeFilterButton}': "[data-active-filter-button]",
		"{filter}": "[data-filter-item]",
		"{filterWrapper}": "[data-filter-wrapper]",

		// Post type filters
		"{postTypeFilterButton}": "[data-filter-post-type-button]",
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

	clicked: false,
	initialLoad: true,

	init: function() {
		self.initFilters('filter');
	},

	initFilters: function(type) {
		var active = self.getActiveFilter();

		// There is a possibility that the active filter has been disabled altogether
		if (active.length > 0) {
			var title = self.getFilterHtml(active);

			self.activeFilterText().html(title);

			self.activeFilterButton().removeClass('is-loading');

			// Only process this on the user dashboard page
			if (opts.type == 'user') {
				// Disabled the news feed filter button first
				self.disabledNewsFeedFilterButton();

				// Disabled post type filter button first
				self.disabledPostTypeFilterButton();
			}
		}
	},

	loadPuller: function() {
		var targetElement = '[data-story-form]';

		// If story form not available, we use the first stream item
		if ($(targetElement).length == 0) {
			targetElement = '[data-stream-item]:first-child';
		}

		window.initPuller = function() {
			return window.es.puller.init({
									mainElement: targetElement,
									triggerElement: targetElement,
									onRefresh: function (done) {
										setTimeout(function () {
											var controller = $('body').controller(EasySocial.Controller.System.Notifier);

											controller.check(true, true);
											done();

										}, 150);
									}
								});
		};

		var puller = initPuller();
	},

	getActiveFilter: function(wrapper) {
		var active = self.filter('.active');

		return active;
	},

	getFilterHtml: function(filter) {
		return filter.find('[data-filter-text]').html();
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

		var options = $.extend({
			"type": filter,

			// Cluster uses filter. We probably need to standardize this
			"filter": filter,
			"postTypes": postTypes,
			"id": filterId,
			"controller": opts.ajaxController,
			"task": opts.ajaxTask,
		}, opts.ajaxOptions);

		options[EasySocial.token()] = 1;

		// Use jquery's ajax method as we need to use the standard html view. #2912
		$.Ajax({
			"url": EasySocial.ajaxUrl,
			"method": 'post',
			"data": options
		}).done(function(contents) {

			var data = JSON.parse(contents);

			var contents = $(data.contents);
			var count = data.count;

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

			if (opts.isMobile) {
				self.loadPuller();
			}

			// 3PD FIX: Kunena [text] replacement
			try {
				MathJax && MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
			} catch(err) {};

		}).fail(function(message) {
			return message;
		}).always(function() {

			// Initialize reactions
			if (window.es.mobile || window.es.tablet) {
				window.es.initReactions();
			}

			// Give a tiny buffer to prevent call stack request from maxing out. #1733
			setTimeout(function() {
				self.clicked = false;

				// Only process this on the user dashboard page
				if (opts.type == 'user') {
					// Re-enabled news feed filter button
					self.enabledNewsFeedFilterButton();

					// Re-enabled post type filter button
					self.enabledPostTypeFilterButton();
				}

				// re-enable the post filter
				self.enabledPostTypeFilter();

			}, 500);

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

	disabledPostTypeFilter: function() {
		self.postTypeFilter().attr('disabled', 'disabled');
	},

	enabledPostTypeFilter: function() {
		self.postTypeFilter().removeAttr('disabled');
	},

	disabledNewsFeedFilterButton: function() {
		self.activeFilterButton().attr('disabled', 'disabled');
	},

	enabledNewsFeedFilterButton: function() {
		self.activeFilterButton().removeAttr('disabled');
	},

	disabledPostTypeFilterButton: function() {
		self.postTypeFilterButton().attr('disabled', 'disabled');
	},

	enabledPostTypeFilterButton: function() {
		self.postTypeFilterButton().removeAttr('disabled');
	},

	// Allows caller to remove all checked post types
	resetPostTypes: function() {
		self.postTypeFilter().removeAttr('checked');
	},

	getPostTypes: function() {
		// Whenever there is a change of post type filter, we just recompute the value
		var postTypes = [];

		self.postTypeFilter(':checked').map(function() {
			postTypes.push($(this).val());
		});

		return postTypes;
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

		self.changePostTypeFilter(element);
	},

	changePostTypeFilter: function(element)
	{
		// Disabled the filter checkbox first
		self.disabledPostTypeFilter();

		var active = self.getActiveFilter();
		var filter = active.data('type');
		var id = active.data('id');

		// Whenever there is a change of post type filter, we just recompute the value
		var postTypes = self.getPostTypes();

		$('body').trigger('onAfterSelectPostType', [element.val()]);

		// Get the current filter on the page
		self.updateStream(filter, id, postTypes);
	},

	"{filter} click": function(filter, event) {

		// Prevent clicking any items more than once
		if (self.clicked) {
			return false;
		}

		// Only process this on the user dashboard page
		if (opts.type == 'user') {
			// Disabled the news feed filter button during loading steam item
			self.disabledNewsFeedFilterButton();

			// Disabled post type filter button during loading steam item
			self.disabledPostTypeFilterButton();
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

		// Only modify the URL if this is not the first page load,
		// Otherwise, it will look like redirection.
		if (!self.initialLoad) {
			anchor.route();
		}

		self.initialLoad = false;

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
				"id": id,
				"uid": opts.uid
			})
		});
	},

	"{createFilter} click": function(element, event) {
		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/stream/getFilterFormDialog', {
				"type": opts.type,
				"uid": opts.uid
			})
		});
	},

	"{saveHashTag} click": function(el) {
		var hashtag = el.data('tag');

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/stream/getFilterFormDialog', {
				"type": opts.type,
				"hashtag": hashtag,
				"uid": opts.uid
			})
		});
	}
}});

module.resolve();

});

});
