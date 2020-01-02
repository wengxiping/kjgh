EasySocial.module('site/apps/reviews/reviews', function($) {

var module = this;


EasySocial.Controller('Apps.Review', {
	defaultOptions: {
		"{wrapper}": "[data-reviews-wrapper]",
		"{contents}": "[data-reviews-contents]",
		"{delete}": "[data-delete]",
		"{approve}": "[data-approve]",
		"{reject}": "[data-reject]",
		"{withdraw}": "[data-withdraw]",
		"{mobileFilter}": "[data-es-mobile-filters] [data-review-filter]",
		"{item}": "[data-review-item]"
	}
}, function(self, opts) { return {

	init: function() {
		opts.id = self.element.data('id');
		opts.uid = self.element.data('uid');
		opts.type = self.element.data('type');
	},

	"{delete} click" : function(el, event) {

		var item = el.parents(self.item().selector);
		var id = item.data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/reviews/confirmDelete', { "id" : id})
		});
	},

	"{approve} click" : function(el, ev) {

		var item = el.parents(self.item().selector);
		var id = item.data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/reviews/confirmApprove', { "id" : id})
		});
	},

	"{reject} click" : function(el, ev) {

		var item = el.parents(self.item().selector);
		var id = item.data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/reviews/confirmReject', { "id" : id})
		});
	},

	"{withdraw} click" : function(el, ev) {

		var item = el.parents(self.item().selector);
		var id = item.data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/reviews/confirmWithdraw', { "id" : id})
		});
	},

	setActiveFilter: function(filter) {
		self.mobileFilter().removeClass('active');
		filter.addClass('active');
	},

	updatingContents: function() {
		self.contents().html('&nbsp;');
		self.wrapper().removeClass('is-empty').addClass('is-loading');
	},

	updateContents: function(html, empty) {
		self.wrapper().removeClass('is-loading');
		self.contents().html(html);

		if (empty) {
			self.wrapper().addClass('is-empty');
		} else {
			self.wrapper().removeClass('is-empty');
		}
	},

	"{mobileFilter} click" : function(el, ev) {
		var type = el.data('review-filter');

		self.setActiveFilter(el);

		self.getItems(type);
	},

	getItems: function(type, callback) {

		self.updatingContents();

		EasySocial.ajax('site/controllers/reviews/getReviews', {
			"id": opts.id,
			"type": opts.type,
			"filter": type
		}).done(function(contents, empty) {
			if ($.isFunction(callback)) {
				callback.call(this, contents, empty);
			}

			self.updateContents(contents, empty);
		});
	}
}});

module.resolve();
});

