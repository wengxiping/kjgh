EasySocial.module('site/events/guests', function($) {

	var module = this;

	EasySocial.Controller('Events.App.Guests', {
		defaultOptions: {

			// Wrapper
			"{wrapper}": "[data-wrapper]",
			"{contents}": "[data-contents]",

			// Item
			"{item}": "[data-item]",

			// Actions
			"{remove}": "[data-guest-remove]",
			"{approve}": "[data-guest-approve]",
			"{promote}": "[data-guest-promote]",
			"{demote}": "[data-guest-demote]",
			"{reject}": "[data-guest-reject]",

			"{searchInput}": "[data-search-input]",

			// Filters
			"{filter}": "[data-filter]"
		}
	}, function(self, opts) { return {

		init : function() {
			// Get the id of the page
			opts.id = self.element.data('id');
			opts.returnUrl = self.element.data('return');
		},

		search: function(keyword) {
			var type = $('[data-filter].active').data('type');

			self.updatingContents();

			EasySocial.ajax('apps/event/guests/controllers/events/getGuests', {
				"id": opts.id,
				"keyword": keyword,
				"filter": type
			}).done(function(contents) {

				self.updateContents(contents);

				if (!self.item().length) {
					self.contents().addClass('is-empty');
				}
			});
		},

		setActiveFilter: function(filter) {
			self.filter().removeClass('active');
			filter.addClass('active');
		},

		getItems: function(type, clusterId, callback) {

			if (self.searchInput().val() != '') {
				this.search(self.searchInput().val());
				return;
			}

			var id = clusterId ? clusterId : opts.id;

			self.updatingContents();

			EasySocial.ajax('apps/event/guests/controllers/events/getGuests', {
				"id": id,
				"filter": type
			}).done(function(contents) {

				if ($.isFunction(callback)) {
					callback.call(this, contents);
				}

				self.updateContents(contents);

				// Show empty if necessary
				self.contents().toggleClass('is-empty', !self.item().length);

				$('body').trigger('afterUpdatingContents', [contents]);

			});
		},

		updatingContents: function() {
			self.wrapper().empty();
			self.contents().addClass('is-loading');
		},

		updateContents: function(html) {
			self.contents().removeClass('is-loading');
			self.wrapper().replaceWith(html);
		},

		"{remove} click" : function(link, event) {

			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/events/confirmRemoveGuest', {"id": userId, "return": returnUrl})
			});
		},

		// Approve a follower
		"{approve} click" : function(link, event) {
			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/events/confirmApproveGuest', {
					"userId": userId,
					"id": opts.id,
					"return": returnUrl
				})
			});
		},

		"{promote} click": function(link, event) {
			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/events/confirmPromoteGuest', {
					"uid" : userId,
					"id" : opts.id,
					"return" : returnUrl
				})
			});
		},

		"{demote} click": function(link, event) {

			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/events/confirmDemoteGuest', {
					"uid" : userId,
					"id" : opts.id,
					"return" : returnUrl
				})
			});
		},

		"{reject} click" : function(link, event) {
			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/events/confirmRejectGuest', {
					"userId": userId,
					"id": opts.id,
					"return": returnUrl
				})
			});
		},

		"{searchInput} keyup": $.debounce(function(textInput){
			var keyword = $.trim(textInput.val());
			self.search(keyword);
		}, 250),
	}});

	module.resolve();
});

