EasySocial.module('site/pages/followers', function($) {

	var module = this;

	EasySocial.Controller('Pages.App.Followers', {
		defaultOptions: {

			// Wrapper
			"{wrapper}": "[data-wrapper]",
			"{contents}": "[data-contents]",
			"{result}": "[data-result]",

			// Item
			"{item}": "[data-item]",

			// Actions
			"{cancelInvite}": "[data-cancel]",
			"{remove}": "[data-remove]",
			"{approve}": "[data-approve]",
			"{promote}": "[data-promote]",
			"{revoke}": "[data-revoke]",
			"{reject}": "[data-reject]",

			// Search
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

		setActiveFilter: function(filter) {
			self.filter().removeClass('active');
			filter.addClass('active');
		},

		search: function(keyword) {
			var type = $('[data-filter].active').data('type');

			self.updatingContents();

			EasySocial.ajax('apps/page/followers/controllers/pages/getFollowers', {
				"id": opts.id,
				"keyword": keyword,
				"type": type
			}).done(function(contents) {

				self.updateContents(contents);

				// Show empty if necessary
				if (!self.item().length) {
					self.result().addClass('is-empty');
				}

			});
		},

		getItems: function(type, clusterId, callback) {

			if (self.searchInput().val() != '') {
				this.search(self.searchInput().val());
				return;
			}

			var id = clusterId ? clusterId : opts.id;

			self.updatingContents();

			EasySocial.ajax('apps/page/followers/controllers/pages/getFollowers', {
				"id": id,
				"type": type
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

		"{cancelInvite} click": function(link, event) {
			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var item = link.closest(self.item.selector);

			EasySocial.ajax('site/controllers/pages/cancelInvite' , {
				"id" : opts.id, "userId" : userId, "return": opts.returnUrl
			}).done(function() {
				item.remove();
			});
		},

		"{remove} click" : function(link, event) {

			// Get the user id
			var userId = link.closest(self.item.selector).data('id');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/pages/confirmRemoveFollower', {"id": opts.id, "userId" : userId, "return": opts.returnUrl})
			});
		},

		// Approve a follower
		"{approve} click" : function(link, event) {
			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				"content": EasySocial.ajax('site/views/pages/confirmApprove', {"id": opts.id, "userId" : userId, "return": returnUrl})
			});
		},

		"{promote} click": function(link, event) {
			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/pages/confirmPromote', { "id" : self.options.id, "userId": userId, "return": returnUrl})
			});
		},

		"{revoke} click": function(link, event) {

			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/pages/confirmDemote', {"id": self.options.id, "userId": userId, "return": returnUrl})
			});
		},

		"{reject} click" : function(link, event) {
			// Get the user id
			var userId = link.closest(self.item.selector).data('id');
			var returnUrl = link.closest(self.item.selector).data('return');

			EasySocial.dialog({
				content: EasySocial.ajax('site/views/pages/confirmReject', {"id": opts.id, "userId": userId, "return": returnUrl})
			});
		},

		"{searchInput} keyup": $.debounce(function(textInput) {

			var keyword = $.trim(textInput.val());
			self.search(keyword);

		}, 250),
	}});

	module.resolve();
});

