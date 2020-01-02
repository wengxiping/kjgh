EasySocial.module('site/videos/browser', function($) {

var module = this;

EasySocial.Controller('Videos.Browser', {
	defaultOptions: {

		"{wrapper}": "[data-wrapper]",
		"{sorting}": "input[name='sorting']",
		"{sortItem}": "[data-sorting]",

		// Videos result
		"{result}": "[data-videos-result]",
		"{list}": "[data-result-list]",

		// Video actions
		"{item}": "[data-video-item]",
		"{deleteButton}": "[data-video-delete]",
		"{featureButton}": "[data-video-feature]",
		"{unfeatureButton}": "[data-video-unfeature]",
		"{createFilter}": "[data-video-create-filter]"
	}
}, function(self, opts, base) { return {

	currentFilter: "",
	currentSorting: "",
	categoryId: null,
	isSort: false,
	clicked: false,

	getVideos: function(callback) {

		if (!self.currentSorting) {
			// Set the current sorting
			self.currentSorting = self.sorting().val();
		}

		if (!self.currentFilter) {
			// Set the current sorting
			self.currentFilter = self.activeFilter.data('type');
		}

		// if still empty the filter, just set to all.
		if (!self.currentFilter) {
			self.currentFilter = "all";
		}

		var isSortReq = self.isSort ? "1" : "0";

		EasySocial.ajax('site/controllers/videos/getVideos',{
			"filter": self.currentFilter,
			"categoryId": self.categoryId,
			"sort": self.currentSorting,
			"uid": opts.uid,
			"type": opts.type,
			"hashtags": opts.hashtag,
			"hashtagFilterId": self.hashtagId,
			"isSort": isSortReq
		}).done(function(output) {

			if ($.isFunction(callback)) {
				callback.call(this, output);
			}

			self.activeFilter.parent().removeClass('is-loading');

			if (self.isSort) {
				self.result().removeClass('is-loading');
				self.list().html(output);
			} else {
				self.wrapper().removeClass('is-loading');
				self.result().html(output);
			}

			$('body').trigger('afterUpdatingContents', [output]);
		});
	},

	"{sortItem} click" : function(sortItem, event) {

		// Get the sort type
		var type = sortItem.data('type');
		self.currentSorting = type;

		self.isSort = true;

		if (!self.hashtagId) {
			self.hashtagId = sortItem.data('tag-id');
		}

		var sortFilter = sortItem.data('filter');

		if (sortFilter == 'category') {
			self.categoryId = sortItem.data('id');
		}

		// Route the item so that we can update the url
		sortItem.route();

		self.result().addClass('is-loading');
		self.list().empty();

		self.getVideos();
	},

	"{deleteButton} click": function(deleteButton, event) {

		var item = deleteButton.parents(self.item.selector);
		var id = item.data('id');
		var returnUrl = deleteButton.data('return');

		var options = {
			"id": id
		};

		if (returnUrl.length > 0) {
			options["return"] = returnUrl;
		}

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/videos/confirmDelete', options)
		});
	},

	"{unfeatureButton} click": function(unfeatureButton, event) {
		var item = unfeatureButton.parents(self.item.selector);
		var id = item.data('id');
		var returnUrl = unfeatureButton.data('return');

		var options = {
			"id": id
		};

		if (returnUrl.length > 0) {
			options["return"] = returnUrl;
		}

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/videos/confirmUnfeature', options)
		});
	},

	"{featureButton} click": function(featureButton, event) {
		var item = featureButton.parents(self.item.selector);
		var id = item.data('id');
		var returnUrl = featureButton.data('return');

		var options = {
			"id": id
		};

		if (returnUrl) {
			options["return"] = returnUrl;
		}

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/videos/confirmFeature', options)
		});
	}
}});

module.resolve();


});
