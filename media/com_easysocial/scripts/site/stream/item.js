EasySocial.module('site/stream/item', function() {

var module	= this;

EasySocial.require()
.library("mentions", "placeholder", "leaflet", "leaflet-providers", "gmaps")
.done(function($) {

EasySocial.Controller('Stream.Item', {
	defaultOptions: {
		// Properties
		id: "",
		context: "",

		// Actions
		"{delete}": "[data-stream-actions] > [data-delete]",

		// Bookmarks
		"{addBookmark}": "[data-bookmark-add]",
		"{removeBookmark}": "[data-bookmark-remove]",

		// Hide actions
		"{hiddenNotice}": "[data-hidden-notice]",
		"{hide}": "[data-hide]",
		"{unhide}": "[data-unhide]",

		// Translations
		"{translate}": "[data-translate]",

		// Sticky items
		"{addSticky}": "[data-sticky-add]",
		"{removeSticky}": "[data-sticky-remove]",

		// Stream content area
		"{contents}": "[data-contents]",
		"{preview}": "[data-preview]",
		"{wrapper}": "[data-wrapper]",

		// Editors
		"{edit}": "[data-edit]",
		"{update}": "[data-edit-update]",
		"{cancelEdit}": "[data-edit-cancel]",
		"{editor}": "[data-editor]",

		// Stream actions
		"{actions}": "[data-stream-actions]",
		"{comment}": "[data-stream-actions] [data-type=comments]",
		"{commentWrapper}": "[data-es-comments]",

		// Polls
		"{editPoll}": "[data-polls-edit]",
		"{cancelEditPoll}"	: "[data-stream-polls-edit-cancel]",
		"{updatePoll}": "[data-stream-polls-edit-update]",

		// Others
		"{publishItem}": "[data-publish]",
		"{likes}": "[data-likes-action]",
		"{counterBar}": "[data-stream-counter]",
		"{likeContent}": "[data-likes-content]",
		"{repostContent}": "[data-repost-content]",

		"{share}": "[data-repost-action]",
		"{locationLink}": "[data-location-link]",
		"{locationPreview}": "[data-location-preview]"

	}
}, function(self, opts, base) { return {

	init: function() {
		// Set the stream's unique id.
		opts.id = base.data('id');
		opts.context = base.data('context');
		opts.hidden = base.data('hidden');
		opts.actor = base.data('actor');
		opts.appid = base.data('appid');
	},

	initMap: function() {

		var locationMap = base.find('[data-location-map]');
		var isEdit = locationMap.hasClass('is-edited');

		if (locationMap.length > 0) {
			var lat = locationMap.data('latitude');
			var lng = locationMap.data('longitude');

			if (lat && lng) {
				if (locationMap.data('location-provider') === 'osm') {
					self.initOsm(locationMap, lat, lng, isEdit);
				} else {
					self.initGmaps(locationMap, lat, lng, isEdit);
				}
			}

			locationMap.removeClass('is-edited');
		}
	},

	marker: {},
	gmap: false,

	initGmaps: function(divEle, lat, lng, isEdit) {
		if (self.gmap !== false && !isEdit) {
			return;
		}

		self.gmap = new $.GMaps({
				div: divEle.get(0),
				lat: lat,
				lng: lng,
				zoom: 15,
				mapTypeId: 'roadmap',
				zoomControl: true,
				clickableIcons: false,
				streetViewControl: false,
				mapTypeControl: false
			});

		// We will remove all markers first (if any)
		self.gmap.removeMarkers();

		// Add the new marker on the map
		var marker = self.gmap.addMarker({
			lat: lat,
			lng: lng
		});

		self.gmap.setCenter(lat, lng);

		var currentZoom = self.gmap.map.zoom;

		// If the current zoom too far,
		// we zoom in a bit
		if (currentZoom < 13) {
			self.gmap.fitZoom();
			self.gmap.zoomOut(9);
		}
	},

	initOsm: function(divEle, lat, lng, isEdit) {

		if (self.osm !== undefined && !isEdit) {
			return;
		}

		self.osm = L.map(divEle.get(0), {
			zoom: 12
		});

		self.osm.fitWorld();

		L.tileLayer.provider('Wikimedia').addTo(self.osm);

		var latlng = {
					lat: parseFloat(lat),
					lng: parseFloat(lng)
				}

		self.osm.removeLayer(self.marker);

		self.osm.flyTo(latlng, 10, {
			"duration": 3
		});

		self.marker = L.marker(latlng).addTo(self.osm);
	},

	getActiveFilter: function() {
		var filterItem = $('[data-filter-item].active');

		return filterItem;
	},

	plugins: {},

	"{locationLink} click": function() {
		self.locationPreview().toggleClass('t-hidden');
		self.initMap();
	},

	"{translate} click": function(link, event) {

		// Get the stream content
		var contents = self.contents().html();
		var translatedWrapper = link.siblings('[data-translations]');

		// Add a loading indicator on the translation link
		self.element.addClass('is-translating');

		EasySocial.ajax('site/controllers/stream/translate', {
			"contents": contents
		}).done(function(html) {

			// Add the translated contents
			translatedWrapper.html(html)

		}).always(function() {

			// Once translated, remove the translate link.
			link.remove();

			self.element
				.removeClass('is-translating')
				.addClass('is-translated');
		});
	},

	"{addBookmark} click": function(el, event) {

		// Add class to the element
		self.element.addClass('is-bookmarked');

		EasySocial.ajax('site/controllers/stream/bookmark', {
			"id" : opts.id
		}).done(function() {

		}).fail(function(message) {
			// If this is failed, we need to display the message object
			self.element.removeClass('is-bookmarked');

			self.setMessage(message);
		});
	},

	"{removeBookmark} click": function(link, event) {

		// Get the active filter type
		var filter = self.getActiveFilter();
		var type = filter.data('type');

		// Always remove the bookmark class
		self.element.removeClass('is-bookmarked');

		EasySocial.ajax('site/controllers/stream/removeBookmark', {
			"id": opts.id
		}).done(function() {
			if (type == 'bookmarks') {
				self.element.remove();
			}
		});
	},

	"{addSticky} click": function(el, event) {
		var recentDivider = $('[data-stream-recent-divider]');
		var stickyDivider = $('[data-stream-sticky-divider]');
		var stickyList = self.element.parent().siblings('[data-stream-sticky-list]');

		EasySocial.ajax('site/controllers/stream/addSticky', {
			"id" : opts.id
		})
		.done(function(){
			self.element.addClass('is-sticky');

			if (stickyDivider.hasClass('t-hidden')) {
				recentDivider.removeClass('t-hidden');
				stickyDivider.removeClass('t-hidden');
				stickyList.removeClass('t-hidden');
			}

			// append the new pinned item
			stickyList.prepend(self.element);
		});;
	},

	"{removeSticky} click": function(el, event) {
		var filter = self.getActiveFilter();
		var type = filter.data('type');

		var recentDivider = $('[data-stream-recent-divider]');
		var stickyDivider = $('[data-stream-sticky-divider]');
		var streamList = self.element.parent().siblings('[data-stream-list]');
		var stickyList = self.element.parent('[data-stream-sticky-list]');

		EasySocial.ajax('site/controllers/stream/removeSticky', {
			"id": self.options.id
		})
		.done(function(){

			self.element.removeClass('is-sticky');

			streamList.prepend(self.element);

			if (stickyList.children().length == 0) {
				recentDivider.addClass('t-hidden');
				stickyDivider.addClass('t-hidden');
				stickyList.addClass('t-hidden');
			}

		});
	},

	"{publishItem} click": function(el, event) {
		var id = opts.id;

		EasySocial.ajax('site/controllers/stream/publish', {
			"id": id
		}).done(function() {

			$pendingFilter = $('[data-filter-item="moderation"]');
			$pendingFilter.removeClass('has-notice');

			// When the stream is published, we want to hide the item
			base.switchClass('is-published');
		});
	},

	"{likes} onLiked": function(el, event, data) {
		self.counterBar().removeClass('hide');
	},

	"{likes} onUnliked": function(el, event, data) {

		var isLikeHide 		= self.likeContent().hasClass('hide');
		var isRepostHide 	= self.repostContent().hasClass('hide');

		if( isLikeHide && isRepostHide )
		{
			self.counterBar().addClass( 'hide' );
		}
	},

	"{share} create": function(el, event, itemHTML) {

		//need to make the data-stream-counter visible
		self.counterBar().removeClass('hide');

	},

	"{comment} click" : function() {
		// Trigger comments
		self.commentWrapper().trigger('show');
	},

	"{delete} click" : function() {

		var uid = opts.id;

		EasySocial.dialog({
			content: EasySocial.ajax('site/views/stream/confirmDelete'),
			bindings: {
				"{deleteButton} click" : function() {

					EasySocial.ajax('site/controllers/stream/delete', {
						"id": uid,
					}).done(function() {

						EasySocial.dialog.close();

						// Remove the element
						self.element
							.fadeOut(400, function() {
								self.element.remove();

								self.parent.trigger('onDeleteStream', [uid]);
							});

					});
				}
			}
		});

	},

	"{editPoll} click" : function(button, event) {
		var uid = self.options.id;
		var element = 'stream';

		EasySocial.ajax('site/views/polls/edit', {
			"uid": uid,
			"element": element,
			"source": 'stream'
		}).done(function(html) {

			// Add editing state
			self.element.addClass('is-editing');

			self.contents().hide();
			self.preview().hide();
			self.locationPreview().hide();
			self.editor().html(html);

		});
	},

	"{cancelEditPoll} click" : function() {
		self.element.removeClass('is-editing');

		self.editor().empty();
		self.contents().show();
		self.preview().show();
		self.locationPreview().show();
	},

	"{updatePoll} click": function() {
		var controller = self.element.find('[data-polls-form]').controller('EasySocial.Controller.Polls.Form');

		var valid = controller.validateForm();

		if (!valid) {
			return task.reject('Error validating polls inputs. Please make sure all the required fields are filled in.');
		}

		// Export the data
		var data = controller.toData();

		EasySocial.ajax('site/controllers/polls/update', data)
			.done(function(preview, id){
				self.preview().html(preview);
				self.cancelEditPoll().click();
			});
	},

	"{cancelEdit} click" : function() {
		self.element.removeClass('is-editing');

		// Remove the contents
		self.editor().empty();

		// Show the contents
		self.contents().removeClass('t-hidden');

		// Show the preview
		self.preview().removeClass('t-hidden');
	},


	"{edit} click" : function() {

		EasySocial.ajax('site/views/stream/edit', {
			"id": opts.id,
			"appid": opts.appid
		}).done(function(html) {

			// Add editing state
			self.element.addClass('is-editing');

			// Hide the stream contents
			self.contents().addClass('t-hidden');

			// hide preview
			self.preview().addClass('t-hidden');
			self.locationPreview().addClass('t-hidden');

			// Append the editor
			self.editor().html(html);

		});
	},

	"{hide} click": function(link, event) {

		var type = link.data('type');
		var multiple = link.data('multiple');

		EasySocial.ajax('site/controllers/stream/hide', {
			"id": opts.id,
			"actor": opts.actor,
			"context": opts.context,
			"type": type
		}).done(function(html) {

			// Hide itself
			self.wrapper().hide();

			// Hide all feeds that belong to the type
			var items = $('[data-stream-item][data-' + type + '="' + opts[type] + '"]');
			items.addClass('t-hidden');

			// Append the message
			self.element.append(html);
			self.element.removeClass('t-hidden');
		});
	},

	"{unhide} click": function(button, event) {
		var parent = button.parents(self.hiddenNotice.selector);
		var type = parent.data('type');

		EasySocial.ajax('site/controllers/stream/unhide', {
			"type": type,
			"actor": opts.actor,
			"context": opts.context,
			"id": opts.id
		}).done(function() {

			// Remove the hidden notice
			self.hiddenNotice('[data-type="' + type + '"]').remove();

			// Show the stream item
			self.wrapper().show();

			// Show all feeds that belong to this actor
			var items = $('[data-stream-item][data-' + type + '="' + opts[type] + '"]');

			if (items.length > 0) {
				items.removeClass('t-hidden');
			}
		});
	},

}});

module.resolve();
});
});
