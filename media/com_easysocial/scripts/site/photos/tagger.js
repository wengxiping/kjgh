EasySocial.module("site/photos/tagger", function($){

var module = this;

var KEYCODE = {
	BACKSPACE: 8,
	COMMA: 188,
	DELETE: 46,
	DOWN: 40,
	ENTER: 13,
	ESCAPE: 27,
	LEFT: 37,
	RIGHT: 39,
	SPACE: 32,
	TAB: 9,
	UP: 38
};

var Controller =
EasySocial.Controller("Photos.Tagger", {
	hostname: "tagger",

	defaultOptions: {
		width: 100,
		height: 100,

		drawTolerance: 30,
		clusterId: false,
		clusterType: false,
		clusterPrivate: false,

		"{viewport}"    : "[data-photo-tag-viewport]",
		"{tagItem}"     : "[data-photo-tag-item]",
		"{tagSelection}": "[data-photo-tag-item].new",
		"{tagButton}"   : "[data-photo-tag-button]",

		"{infoTagListItemGroup}": "[data-photoinfo-tag-list-item-group]",
		"{tagListItemGroup}": "[data-photo-tag-list-item-group]",
		"{tagListItem}"     : "[data-photo-tag-list-item]",

		"{tagRemoveButton}" : "[data-photo-tag-remove-button]"
	}
}, function(self, opts) { return {

	init: function() {
		// Get the tagging template first
		opts.tagItemTemplate = $('[data-tag-form-wrapper]').children().clone();
	},

	newTagItem: function() {

		// Use existing tag item if created
		var viewport = self.viewport();
		var newTagItem = self.tagItem(".new");

		// Else create one
		if (newTagItem.length < 1) {
			newTagItem = opts.tagItemTemplate.clone()
							.addClass("new")
							.appendTo(viewport);

			self.addSubscriber(
				newTagItem.addController("EasySocial.Controller.Photos.Tag", {
					clusterId: opts.clusterId,
					clusterType: opts.clusterType,
					clusterPrivate : opts.clusterPrivate
				})
			);
		}

		return newTagItem;
	},

	"{tagButton} click": function(tagButton) {
		self[tagButton.data("photoTagButton") || "toggle"]();
	},

	disabled: true,

	enable: function() {

		// This prevents image link from being clicked
		self.photo.imageLink()
			.on("click.tagger", function(event){
				event.preventDefault();
			});

		self.disabled = false;
		self.element.addClass("tagging");

		// // If there is scrollTo
		// if ($.scrollTo) {
		// 	$.scrollTo(self.photo.content(), 250, {offset: {top: -100}});
		// }

		self.trigger("tagEnter");
	},

	disable: function() {

		self.photo.imageLink()
			.off("click.tagger");

		// Remove tag selection
		self.tagSelection().remove();

		// Unfocus any tags which are in focus
		self.tagItem(".focus").removeClass("focus");

		self.disabled = true;
		self.element.removeClass("tagging");

		self.trigger("tagLeave");
	},

	toggle: function() {

		self[(self.disabled) ? "enable" : "disable"]();
	},

	area: {},

	calculateArea: function(collision, offset) {

		// Normalize arguments
		if (!collision) { collision = "clip" };
		if (!offset)    { offset = {x: 0, y: 0} };

		// Calculate image area
		var viewportEl   = self.viewport(),
			viewport        = viewportEl.offset();
			viewport.width  = viewportEl.width();
			viewport.height = viewportEl.height();
			viewport.right  = viewport.width  + viewport.left;
			viewport.bottom = viewport.height + viewport.top;

		// Calculate area relative to screen
		// top, left, width, height, right, bottom
		var area = self.area;
		area.top    = ((area.startY <= area.endY) ? area.startY : area.endY) + offset.y;
		area.left   = ((area.startX <= area.endX) ? area.startX : area.endX) + offset.x;
		area.width  = Math.abs(area.endX - area.startX);
		area.height = Math.abs(area.endY - area.startY);
		area.right  = area.width  + area.left;
		area.bottom = area.height + area.top;

		// Collision handling
		if (collision=="clip") {

			// Cap area within image boundaries
			if (area.top    <= viewport.top   ) {area.top    = viewport.top;   }
			if (area.bottom >= viewport.bottom) {area.bottom = viewport.bottom;}
			if (area.left   <= viewport.left  ) {area.left   = viewport.left;  }
			if (area.right  >= viewport.right ) {area.right  = viewport.right; }

			// Resize tag
			area.width  = area.right  - area.left;
			area.height = area.bottom - area.top;
		}

		// Reposition tag
		if (collision=="flip") {

			if (area.top <= viewport.top) {
				area.top = viewport.top;
			}

			if (area.left <= viewport.left) {
				area.left = viewport.left;
			}

			if (area.right >= viewport.right) {
				area.right = viewport.right;
				area.left  = area.right - area.width;
			}

			if (area.bottom >= viewport.bottom) {
				area.bottom = viewport.bottom;
				area.top    = area.bottom - area.height;
			}
		}

		// Pixel unit
		area.pixel = {
			top   : area.top  - viewport.top,
			left  : area.left - viewport.left,
			width : area.width,
			height: area.height
		};

		// Decimal unit
		area.decimal = {
			top   : area.pixel.top  / viewport.height,
			left  : area.pixel.left / viewport.width,
			width : area.width      / viewport.width,
			height: area.height     / viewport.height
		}

		// Percentage unit
		area.percentage = {
			top   : (area.decimal.top    * 100) + "%",
			left  : (area.decimal.left   * 100) + "%",
			width : (area.decimal.width  * 100) + "%",
			height: (area.decimal.height * 100) + "%"
		};

		// Decide whether tag should be on custom size
		var tolerance = self.options.drawTolerance;

		self.autodraw =
			(area.width  < tolerance &&
			 area.height < tolerance);

		return area;
	},

	setPivot: function(type, x, y) {

		var area = self.area;
			area[type + "X"] = x;
			area[type + "Y"] = y;
	},

	drawing: false,

	autodraw: false,

	drawTag: function() {

		var area = self.calculateArea();
		var options = self.options;

		if (self.autodraw) {

			area.endX = area.startX + options.width;
			area.endY = area.startY + options.height;

			self.calculateArea("flip", {
				x: options.width / -2,
				y: options.height / -2
			});
		}

		self.newTagItem()
			.css(area.percentage)
			.trigger("focusInput");
	},

	"{viewport} mousedown": function(viewport, event) {

		if (self.disabled) {
			return;
		}

		if (event.target!==viewport[0]) {
			return;
		}

		event.preventDefault();

		// Hide last created tag item which are curresntly in focus
		self.tagItem(".focus")
			.removeClass("focus");

		self.drawing = true;
		self.setPivot("start", event.pageX, event.pageY);


		$(document)
			.on("mousemove.tagger", function(event) {
				if (!self.drawing) {
					return;
				}

				self.setPivot("end", event.pageX, event.pageY);
				self.drawTag();
			})
			.on("mouseup.tagger", function(event) {

				self.setPivot("end", event.pageX, event.pageY);
				self.drawTag();
				$(document).off("mousemove.tagger mouseup.tagger");
			});
	},

	createTag: function(data) {

		var data = $.extend(
			{photo_id: self.photo.id}, data, self.area.decimal
		);

		var task = EasySocial.ajax("site/controllers/photos/createTag", data);

		self.trigger("tagCreate", [task, data, self]);

		return task;
	},

	removeTag: function(id) {

		var task = EasySocial.ajax("site/controllers/photos/removeTag", {id: id});

		self.trigger("tagRemove", [task, id, self]);

		return task;
	},

	addTag: function(data, tagItemHtml, tagListItemHtml, infoTagListItemHtml) {

		// Add tag to viewport and focus on tag
		var tagItem =
			$.buildHTML(tagItemHtml)
				.addClass("focus")
				.appendTo(self.viewport());

		// Add tag list item to tag list
		var tagListItem =
			$.buildHTML(tagListItemHtml)
				.appendTo(self.tagListItemGroup());

		//before we append the tag into info, we need to check if there is any tags or not. if not, we will apend a dash
		var taglen = self.infoTagListItemGroup().children().length;

		if (taglen == 0) {
			self.infoTagListItemGroup().append(' - ');
		}

		// Add tag list item to tag list at info section
		var infoTagListItem =
			$.buildHTML(infoTagListItemHtml)
				.appendTo(self.infoTagListItemGroup());

		self.trigger("tagAdd", [data, tagItem, tagListItem, self]);
	},

	"{self} avatarEnter": function() {

		// When entering avatar mode, hide all tags.
		self.tagItem().hide();

		// Disable tagging mode
		self.disable();
	},

	"{self} avatarLeave": function() {

		// When leaving avatar mode, display all tags.
		self.tagItem().show();
	},

	"{tagRemoveButton} click": function(button, event) {

		var tagId = button.data("photoTagId");

		self.removeTag(tagId);

		event.stopPropagation();
	},

	// Give priority to remove button,
	// make tag viewport appear above of
	// navigation buttons when they are hovered.
	"{tagRemoveButton} mouseover": function() {
		self.viewport().addClass("active");
	},

	"{tagRemoveButton} mouseout": function() {
		self.viewport().removeClass("active");
	}

}});

$.template('tags/menu.item', '<div class="es-photo-tag-menu-item" data-photo-tag-menu-item><div class="es-photo-tag-menu-name"><img src="[%= item.avatar %]" />[%= item.title %]</div></div>');

EasySocial.Controller("Photos.Tag", {
	defaultOptions: {
		clusterId: false,
		clusterType: false,
		clusterPrivate: false,

		"{form}": "[data-photo-tag-form]",
		"{title}": "[data-photo-tag-title]",
		"{removeButton}": "[data-photo-tag-remove-button]",
		"{textField}": "[data-photo-tag-input]",
		"{menu}": "[data-photo-tag-menu]",
		"{menuItem}": "[data-photo-tag-menu-item]"
	}
}, function(self, opts) { return {

	init: function() {
		self.data = opts.data;
	},

	"{self} focusInput": function() {
		self.textField().focus();
	},

	"{textField} keyup": $._.debounce(function(el, event) {

		var keyword = $.trim(self.textField().val());

		var unwantedKeys = [KEYCODE.UP, KEYCODE.DOWN, KEYCODE.ENTER, KEYCODE.ESCAPE];

		if ($.inArray(event.keyCode, unwantedKeys) != -1) {
			return;
		}

		// Build a list of users to exclude
		var users = self.tagger.photo.tags.getTaggedUsers();

		// Determine if we should get the result from cluster member only
		var namespace = 'site/controllers/friends/suggest';
		var options = {
				"search": keyword,
				"exclude": users,
				"includeme": 1,
				"privacy": "photos.tagme"
		}

		if (opts.clusterId && opts.clusterType && opts.clusterPrivate) {
			namespace = 'site/controllers/friends/suggestClusterMembers';
			options = $.extend(options, {
				"clusterId" : opts.clusterId,
				"clusterType": opts.clusterType
			});
		}

		EasySocial.ajax(namespace, options).done(self.render());

	}, 250),

	"{textField} keypress": function(textField, event) {

		var keyword = $.trim(self.textField().val());

		// Get active menu item
		var activeMenuItem = self.menuItem(".active");

		switch (event.keyCode) {

			// If up key is pressed
			case KEYCODE.UP:

				// Deactivate all menu item
				self.menuItem().removeClass("active");

				// If no menu items are activated,
				if (activeMenuItem.length < 1) {

					// activate the last one.
					self.menuItem(":last").addClass("active");

				// Else find the menu item before it,
				} else {

					// and activate it.
					activeMenuItem.prev(self.menuItem.selector)
						.addClass("active");
				}

				event.preventDefault();
				break;

			// If down key is pressed
			case KEYCODE.DOWN:

				// Deactivate all menu item
				self.menuItem().removeClass("active");

				// If no menu items are activated,
				if (activeMenuItem.length < 1) {

					// activate the first one.
					self.menuItem(":first").addClass("active");

				// Else find the menu item after it,
				} else {

					// and activate it.
					activeMenuItem.next(self.menuItem.selector)
						.addClass("active");
				}

				event.preventDefault();
				break;

			// If enter is pressed
			case KEYCODE.ENTER:

				// Use menu item
				if (activeMenuItem.length > 0) {

						activeMenuItem.trigger("click");

					// Create custom label
					} else {
					self.create({
						type: "label",
						label: keyword
					});
					};

				self.menu().hide();
				break;

			// If escape is pressed,
			case KEYCODE.ESCAPE:

				// hide menu.
				self.menu().hide();
				break;
		}
	},

	"{menuItem} mouseover": function(menuItem) {

		self.menuItem().removeClass("active");

		menuItem.addClass("active");
	},

	"{menuItem} mouseout": function(menuItem) {

		self.menuItem().removeClass("active");
	},

	render: $.Enqueue(function(items) {

		var menu = self.menu();

		if (!items || items.length < 1) {
			menu.hide();
			return;
		}

		menu.empty();

		$.each(items, function(i, item) {
			var itemTemplate = $('<div/>').html(item);
			var prop = {
				"title": itemTemplate.find('[data-suggest-title]').val(),
				"avatar": itemTemplate.find('[data-suggest-avatar]').attr('src'),
				"id": itemTemplate.find('[data-suggest-id]').val()
			};

			var tmpl = $.View('tags/menu.item', {"item": prop});

			$(tmpl)
				.data("item", prop)
				.appendTo(menu);


			menu.show();
		});
	}),

	create: function(data) {

		var tag = self.element;

		// Store tag data
		self.data = data;

		// Update tag title
		self.title()
			.html(data.label);

		// Do not submit empty label
		if ($.trim(data.label)==="") return;

		// Create tag
		self.tagger.createTag(data)
			.done(function(tag, tagItemHtml, tagListItemHtml, infoTagListItemHtml){

				// Add new tag
				self.tagger.addTag(tag, tagItemHtml, tagListItemHtml, infoTagListItemHtml);

				// Destroy myself
				self.element.remove();
			})
			.fail(function(message){
				tag.remove();
			});
	},

	remove: function() {

		var tag = self.element;
		var tagId = (self.data || {}).id;

		// If this is a new tag, just remove element;
		if (!tagId) return tag.remove();

		// Remove tag
		self.tagger.removeTag(tagId)
			.done(function(){
				tag.remove();
			});
	},

	"{menuItem} click": function(menuItem, event) {
		event.preventDefault();
		event.stopPropagation();

		var item = menuItem.data("item");

		self.create({
			"uid": item.id,
			"type": "person",
			"label": item.title,
		});
	},


	"{removeButton} click": function() {
		self.remove();
	}

}});

module.resolve(Controller);

});
