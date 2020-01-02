EasySocial.module('site/manage/clusters', function($) {

var module 	= this;

EasySocial.Controller('Clusters', {
	defaultOptions: {

		"{activeButton}": "[data-active-filter-button]",
		"{activeText}": "[data-active-filter-text]",

		// Filters
		"{filter}": "[data-filter-item]",
		"{filterText}": "[data-filter-item-text]",

		// Content area.
		"{wrapper}": "[data-wrapper]",
		"{contents}": "[data-contents]",

		// Result
		"{items}": "[data-items]",
		"{item}": "[data-item]",
		"{pagination}": "[data-pagination]",

		// Actions
		"{approve}": "[data-approve]",
		"{reject}": "[data-reject]",

		// Counters
		"{counters}": "[data-counter]"
	}
}, function(self, opts) { return {

	init: function() {
		self.initDefaultFilter();
	},

	initDefaultFilter: function() {
		var activeFilter = self.filter('.active');

		self.setActiveFilter(activeFilter);
	},

	updateCounter: function() {
		EasySocial.ajax('site/controllers/manage/getClusterCounters')
			.done(function(counters) {

				self.filter('[data-type="event"]')
					.find(self.counters.selector)
					.html(counters['event']);

				self.filter('[data-type="group"]')
					.find(self.counters.selector)
					.html(counters['group']);

				self.filter('[data-type="page"]')
					.find(self.counters.selector)
					.html(counters['page']);
			});
	},

	removeItem: function(id) {
		// Remove item from the list.
		var item = self.item('[data-id="' + id + '"]');

		item.remove();

		if (self.item().length <= 0) {
			self.items().addClass('is-empty');
			self.pagination().remove();
		}

		// Update the counter for the list items.
		self.updateCounter();
	},

	// Update the content on the items list.
	updateContents: function(html) {
		self.contents().html(html);

		$('body').trigger('afterUpdatingContents', [html]);
	},

	setActiveFilter: function(filter) {
		var text = filter.find(self.filterText.selector).clone();

		self.activeText().html(text);
		self.activeButton().removeClass('is-loading');

		// Remove all active classes
		self.filter().removeClass('active');

		// Add active class on itself
		filter.addClass('active');
	},

	"{filter} click" : function(filter, event) {
		event.preventDefault();
		event.stopPropagation();

		var type = filter.data('type');

		// Remove all active state on the filter links.
		self.setActiveFilter(filter);

		// Set the browsers attributes
		var anchor = filter.find('> a');
		anchor.route();

		self.wrapper().addClass('is-loading');
		self.contents().empty();

		// Simulate the click on the active button so we can hide the dropdown
		self.activeButton().click();

		EasySocial.ajax("site/controllers/manage/filterCluster", {
			"filter": type
		}).done(function(html){
			self.updateContents(html);

		}).always(function(){

			self.wrapper().removeClass('is-loading');
			filter.removeClass("is-loading");
		});
	},

	// Approve
	"{approve} click" : function(link, event) {
		// Get the cluster id
		var clusterId = link.closest(self.item.selector).data('id');
		var clusterType = link.closest(self.item.selector).data('type');

		EasySocial.dialog({
			content	: EasySocial.ajax("site/views/manage/confirmClusterApprove" , {"clusterId" : clusterId, "clusterType" : clusterType}),
			selectors: {
				"{sendMail}": "[data-send-email]",
				"{approveButton}": "[data-approve-button]",
				"{cancelButton}": "[data-cancel-button]"
			},
			bindings : {
				"{approveButton} click" : function() {

					var sendMail = this.sendMail().is(':checked') ? 1 : 0;

					EasySocial.ajax('site/controllers/manage/approveCluster', {
						"clusterId": clusterId,
						"clusterType": clusterType,
						"sendMail": sendMail
					})
					.done(function() {
						self.removeItem(clusterId);
						EasySocial.dialog().close();
					});
				},

				"{cancelButton} click": function() {
					EasySocial.dialog().close();
				}
			}
		});

	},

	// Reject
	"{reject} click" : function(link, event) {

		// Get the cluster id
		var clusterId = link.closest(self.item.selector).data('id');
		var clusterType = link.closest(self.item.selector).data('type');

		EasySocial.dialog({
			content: EasySocial.ajax("site/views/manage/confirmClusterReject" , {"clusterId" : clusterId, "clusterType": clusterType}),
			selectors: {
				"{rejectMessage}": "[data-reject-message]",
				"{rejectButton}": "[data-reject-button]",
				"{cancelButton}": "[data-cancel-button]",
				"{sendMail}": "[data-send-email]",
				"{deleteCluster}": "[data-delete-cluster]"
			},
			bindings : {
				"{rejectButton} click" : function() {
					var rejectMessage = this.rejectMessage().val();
					var sendMail = this.sendMail().is(':checked') ? 1 : 0;
					var deleteCluster = this.deleteCluster().is(':checked') ? 1 : 0;

					EasySocial.ajax('site/controllers/manage/rejectCluster', {
						"clusterId": clusterId,
						"clusterType": clusterType,
						"rejectMessage": rejectMessage,
						"sendMail": sendMail,
						"deleteCluster": deleteCluster
					})
					.done(function() {
						self.removeItem(clusterId);
						EasySocial.dialog().close();
					});
				},

				"{cancelButton} click": function() {
					EasySocial.dialog().close();
				}
			}
		});
	}
}});

module.resolve();
});
