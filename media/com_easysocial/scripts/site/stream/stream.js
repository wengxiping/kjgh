EasySocial.module('site/stream/stream', function(){

var module = this;

EasySocial.require()
.script('site/stream/item')
.done(function($) {

EasySocial.Controller('Stream', {

	defaultOptions: {
		// Properties
		checknew: null,
		source: null,
		sourceId: null,
		clusterId: null,
		clusterType: null,
		autoload: true,

		// Elements
		"{story}": "[data-story]",
		"{repost}": "[data-repost-action]",

		// Notification bar to notify users of new stream items
		"{updatesBar}": "[data-updates-bar]",
		"{newUpdatesButton}": "[data-updates-button]",

		"{newNotiBar}": "[data-stream-notification-bar]",
		"{newNotiButton}": "[data-stream-notification-button]",

		// Stream lists
		"{list}": "[data-stream-list]",
		"{item}": "[data-stream-item]",

		// Pagination
		"{pagination}": "[data-pagination]"
	}
}, function(self, opts) { return {

	init : function() {

		// Implement stream item controller.
		self.item().addController(EasySocial.Controller.Stream.Item, {
			"{parent}": self
		});

		// Auto load streams when scroll
		if (opts.autoload == true) {

			self.on("scroll.stream", window, $._.debounce(function(){

				// If it is loading, do not run it again
				if (self.loading) {
					return;
				}

				if (!self.pagination().visible()) {
					return;
				}

				// Only run the load more when it's visible
				self.loadMore();

			}, 250));

			if (window.es.mobile) {

				// for mobile touch move
				self.on("touchmove", self.element, $._.debounce(function(){
					// If it is loading, do not run it again
					if (self.loading) {
						return;
					}

					// console.log(self.pagination(), document.body.scrollHeight);
					if (!self.pagination().visible()) {
						return;
					}

					// Only run the load more when bottom of the page
					self.loadMore();
				}, 250));
			}

		}

		self.setLayout();

		// listening to notifier.updates trigger
		$(window).on('notifier.updates', function(event, data) {

			if (data.stream == undefined) {
				return;
			}

			var total = data.stream.total;
			var streamData = data.stream.data;
			var nextupdate = streamData.startdate;
			var forceDisplay = data.forceDisplay;

			// update current date
			self.element.data('currentdate', nextupdate);

			if (total <= 0) {
				return;
			}

			var itemData = streamData.data;

			$.each(itemData, function(i, item) {

				if (item.cnt > 0) {

					// Try to find if there are any filter items that matches the criteria
					if (item.type == 'group' || item.type == 'event' || item.type == 'page') {
						item.type = 'feeds';
					}

					var filterItem = $('[data-filter-item][data-type="' + item.type + '"]');
					var counter = filterItem.find('[data-counter]');

					// Get the current count
					var currentCount = counter.text();

					// Get the correct counter to be displayed
					var totalCount = item.cnt;

					if (currentCount) {
						totalCount = parseInt(currentCount, 10) + parseInt(item.cnt, 10);
					}

					// Update the counter
					counter.html(totalCount);
					filterItem.addClass('has-notice');
				}
			});

			var newUpdatesButton = $.trim(streamData.contents);
			var currentContents = $.trim(self.updatesBar().text());

			// display the 'new feed bar' when there is new counter and this new feed bar is not display before.
			if (newUpdatesButton.length > 0 && currentContents.length == 0) {

				if (forceDisplay) {
					var type = $(newUpdatesButton).data('type');
					var uid = $(newUpdatesButton).data('uid');
					var currentdate = $(newUpdatesButton).data('since');

					self.getNewStreams(uid, type, currentdate);

					return;
				}


				self.updatesBar().html(newUpdatesButton);
			}
		});


		// listening to notifier.collection trigger
		$(window).on('notifier.collection', function(event, data) {

			if (!opts.checknew) {
				return;
			}

			// Find the active sidebar.
			var activeSidebar = $('[data-filter-item].active');
			var type = activeSidebar.data('type');
			var id = activeSidebar.data('id');
			var currentdate = self.element.data('currentdate');
			var excludeIds = self.element.data('excludeids');

			var contents = $.trim(self.updatesBar().html());

			if (type == undefined && id == undefined && opts.source == 'profile') {
				type = 'me';
				id = opts.sourceId;
			}

			//wrap up the data.
			var info = {
				"type": type,
				"id": id,
				"currentdate": currentdate,
				"exclude": excludeIds,
				"source": opts.source,
				"view": opts.source
			};

			data.stream = info;
		});

	},

	setLayout: function() {
		// Does nothing for now
	},


	getIdentifier: function() {
		return self.element.data('identifier');
	},

	getActiveFilter: function() {

		// lets try to get the filter item with identifier.
		var filterItem = $("[data-filter-item][data-stream-identifier='" + self.getIdentifier() + "'].active");

		// We know these view won't have filter, just skip this get filter element from page.
		if (opts.source == 'profile' || opts.source == 'profiles') {
			return filterItem;
		}

		if (filterItem == undefined || filterItem.length == 0) {
			var filterItem = $("[data-filter-item].active");
		}

		return filterItem;
	},

	loadMore: function() {

		var filter = self.getActiveFilter();

		var type = filter.data('type');
		var id = filter.data('id');
		var anyid = filter.data('anyid');
		var tag = filter.data('tag');
		var matchAllTags = filter.data('tagMatchAll');

		// Get the pagination attributes
		var startlimit = self.pagination().data('nextlimit');
		var customlimit = self.pagination().data('customlimit');
		var context = self.pagination().data('context');
		var excludeStreamIds = self.pagination().data('excludeStreamids');
		var source = opts.source;

		if (!startlimit) {
			return;
		}

		// User profile view
		if (type == undefined && id == undefined && opts.source == 'profile') {
			type = 'me';
			id = opts.sourceId;
		}

		// Profile type view eg: registered user
		if (type == undefined && id == undefined && opts.source == 'profiles') {
			type = 'profile';
			id = opts.sourceId;
		}

		// Set the current loading state
		self.loading = true;

		// Add loading indicator
		self.pagination().addClass('is-loading');

		// Determines if this stream listing is for a cluster
		var cluster = self.element.data('cluster');

		var options = {
				"id": id,
				"anyid": anyid,
				"type": type,
				"startlimit": startlimit,
				"customlimit": customlimit,
				"source": source,
				"tag": tag,
				"matchAllTags": matchAllTags,
				"context": context,
				"iscluster": cluster,
				"clusterId": opts.clusterId,
				"clusterType": opts.clusterType,
				"excludeStreamIds": excludeStreamIds,
				"controller": "stream",
				"task": "loadmore",
		};

		options[EasySocial.token()] = 1;

		// Use jquery's ajax method as we need to use the standard html view
		$.Ajax({
			"url": EasySocial.ajaxUrl,
			"method": 'post',
			"data": options
		}).done(function(contents) {
			var data = JSON.parse(contents);

			var contents = $(data.contents);
			var nextlimit = data.nextlimit;

			// Update start & end date
			self.pagination().data('nextlimit', nextlimit);

			var contents = $.buildHTML(contents);
			contents
				.appendTo(self.list())
				.filter(self.item.selector)
				.addController(EasySocial.Controller.Stream.Item);

			self.setLayout();

			// add support to kunena [tex] replacement.
			try {
				MathJax && MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
			} catch(err) {};

			if (nextlimit == "") {
				self.pagination().remove();
			}
		})
		.always(function() {

			// Initialize reactions
			if (window.es.mobile || window.es.tablet) {
				window.es.initReactions();
			}

			self.pagination().removeClass('is-loading');

			self.loading = false;
		});
	},

	insertItem: function(html) {
		// Update the current date so that the next new stream notification will not include this item.
		self.updateCurrentDate();

		$.buildHTML(html)
			.prependTo(self.list())
			.addController(EasySocial.Controller.Stream.Item, {
				"{parent}": self
			});

		// Since something is added to the stream, it should never be empty
		self.element.removeClass('is-empty');
	},

	"{window} resize": $.debounce(function() {

		self.setLayout();

	}, 500),

	"{self} onDeleteStream": function(element, event, uid) {

		if (self.item().length <= 0) {
			self.element.addClass('is-empty');
		}
	},

	"{repost} create": function(element, event, itemHTML) {

		// Insert the item to the list
		self.insertItem(itemHTML);
	},

	"{story} create": function(element, event, html, id) {

		if (id) {
			// we need to increate the nextlimit
			var currentStartLimit = self.pagination().data('nextlimit');

			// if its already a negative, mean this page has no more stream to loadmore.
			if (currentStartLimit > 0) {
				currentStartLimit++;
				self.pagination().data('nextlimit', currentStartLimit);
			}
		}

	},

	updateCurrentDate: function() {

		EasySocial.ajax('site/views/stream/getCurrentDate', {
		}).done(function(currentdate) {
			// update next start date
			self.element.data('currentdate', currentdate);
		}).fail(function(messageObj) {

		});

	},

	updateExcludeIds: function(id) {
		var ids = self.element.data('excludeids');
		var newIds = '';

		if (ids != '' && ids != undefined) {
				newIds = ids + ',' + id;
		} else {
			newIds = id;
		}

		self.element.data('excludeids', newIds);
	},

	clearExcludeIds: function() {
		self.element.data('excludeids', '');
	},

	getNewStreams: function(uid, type, currentdate) {
		EasySocial.ajax('site/controllers/stream/getUpdates', {
			"type": type,
			"id": uid,
			"currentdate": currentdate,
			"source": opts.source,
			"view": opts.source
		}).done(function(contents, nextupdate, streamIds) {

			// Get the active filter item
			var filterItem = $('[data-filter-item].active');
			var counter = filterItem.find('[data-counter]');

			// Clear the counter value since we are now retrieving the new stuffs
			if (counter.length > 0) {
				filterItem.removeClass('has-notice');
				counter.html('0');
			}

			// Lets remove the stream items from the page if there is any
			$.each(streamIds, function(i, uid) {
				self.item().where('id', uid).remove();
			});

			var itemCount = streamIds.length;
			var	startlimit = self.pagination().data("nextlimit");
			startlimit = startlimit + itemCount;

			self.pagination().data('nextlimit', startlimit);

			// Clear the new feeds notification.
			self.updatesBar().empty();

			// append stream into list.
			var contents = $.buildHTML(contents);

			contents.prependTo(self.list())
				.addController(EasySocial.Controller.Stream.Item);

			 // Clear the exclude ids
			 self.clearExcludeIds();

			 // Update the next update date
			 self.element.data('currentdate', nextupdate);
		});
	},

	"{newUpdatesButton} click": function(button, event) {
		var type = button.data('type');
		var uid = button.data('uid');
		var currentdate = button.data('since');

		self.getNewStreams(uid, type, currentdate);
	},


	loadMoreGuest: function() {
		var pagination = self.paginationGuest();
		var startlimit = pagination.data("nextlimit");

		var view = opts.source;

		if (!startlimit) {
			return;
		}

		self.loading = true;

		pagination.html(self.view.loadingContent({content: ""}));

		var options = {
				"controller": "stream",
				"task": "loadmoreGuest",
				"view": view,
				"startlimit": startlimit
		};

		options[EasySocial.token()] = 1;

		// Use jquery's ajax method as we need to use the standard html view
		$.Ajax({
			"url": EasySocial.ajaxUrl,
			"method": 'post',
			"data": options
		}).done(function(contents) {
			var data = JSON.parse(contents);

			var contents = $(data.contents);
			var nextlimit = data.nextlimit;

			// Update start & end date
			pagination.data({
				nextlimit: nextlimit
			});

			var contents = $.buildHTML(contents);

				contents
					.insertBefore(pagination)
					.filter(self.item.selector)
					.addController("EasySocial.Controller.Stream.Item");

			// add support to kunena [tex] replacement.
			try { MathJax && MathJax.Hub.Queue(["Typeset",MathJax.Hub]); } catch(err) {};

			//if (opts.autoload || nextlimit=="") {
			if (nextlimit=="") {
				pagination.empty();
			} else {
				//append the anchor link.
				pagination.html(self.view.loadmoreContent());
			}
		})
		.always(function(){
			self.loading = false;
		});
	},

	"{pagination} click" : function() {
		self.loadMore();
	}
}});

module.resolve();
});

});
