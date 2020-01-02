EasySocial.module('site/albums/filter', function($) {

var module = this;

EasySocial.require()
.script('site/albums/browser')
.done(function($) {

EasySocial.Controller('Albums.Filter', {
	defaultOptions: {
		"{sidebarWrapper}": "[data-es-album-filters]",
		"{filter}": "[data-es-album-filters] [data-album-list-item]",
		"{filterLink}" : "[data-album-list-item] > a",

		"{filterTitle}" : "[data-album-list-item-title]",
		"{filterCover}" : "[data-album-list-item-cover]",
		"{filterCount}" : "[data-album-list-item-count]"
	}
}, function(self, opts) { return {

	init: function() {

		// Bind known event
		self.bindEvent();
	},

	bindEvent: function() {
		var controller = self.getController();

		controller.albumItem().on('titleChange', function(event, title, album) {
			self.getFilterItem(album.id, "title").html($.trim(title) || "&nbsp;");
		});

		controller.albumItem().on('photoAdd', function(event, photoItem, photoData, album) {
			self.updateListItemCount(album.id, 1, true);
		});

		controller.albumItem().on('photoMove', function(event, task, photo, batchAction, targetAlbumId) {
			task
			.done(function() {
				// Update current album count
				self.updateListItemCount(photo.album.id, -1, true);

				// Also update targetted album count
				self.updateListItemCount(targetAlbumId, 1, true);
			});
		});

		controller.albumItem().on('photoDelete', function(event, task, photo) {
			task
			.done(function(){
				self.updateListItemCount(photo.album.id, -1, true);
			});
		});
	},

	getController: function() {
		var albumLists = self.element.find('[data-album-browser=' + self.getUuid() + ']');
		var controller = albumLists.controller();

		return controller;
	},

	getUuid: function() {
		return self.sidebarWrapper().data('album-uuid');
	},

	setActiveFilter: function(filter) {
		self.filter().removeClass('active');

		filter.addClass('active');

		// Update the URL on the browser
		filter.find('a').route();

		// Set loading on the correct filter
		filter.addClass('is-loading');
	},

	getFilterItem: function(albumId, context) {

		var filter =
			(!albumId) ?
				self.filter(".new") :
				self.filter().filterBy("albumId", albumId);

		if (!context) return filter;

		return filter.find(self["filter" + $.String.capitalize(context)].selector);
	},

	updateListItemCount: function(albumId, val, append) {

		var stat = self.getFilterItem(albumId, "count");

		// If no stat element found, stop.
		if (stat.length < 0) return;

		// Get current stat count
		var statCount;

		if (append) {
			statCount = (parseInt(stat.text()) || 0) + (parseInt(val) || 0);
		} else {
			statCount = val;
		}

		// Always stays at 0 if less than that
		if (statCount < 0) statCount = 0;

		// Update stat count
		stat.text(statCount);
	},

	"{filterLink} click": function(filterItemLink, event) {

		// Progressive enhancement, no longer refresh the page.
		event.preventDefault();

		// Prevent item from getting into :focus state
		filterItemLink.blur();
	},

	"{filter} click": function(filter, event) {
		var controller = self.getController();

		if (controller === undefined) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		// Don't do anything on new album item
		if (filter.hasClass("new")) {
			return;
		}

		// Set active filter state
		self.setActiveFilter(filter);

		var albumId = filter.data("albumId");

		// Load album
		var loader = controller.open("Album", albumId);
		loader.done(function() {
			filter.removeClass('is-loading');
		});
	}

}});

module.resolve();

});

});
