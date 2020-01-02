EasySocial.module("site/story/story", function($){
var module = this;

// Non-essential dependencies
EasySocial.require()
.script(
	"site/story/gmaps",
	"site/story/friends",
	"site/story/mood",
	"site/story/osm"
);

EasySocial.require()
.library("mentions", "placeholder")
.done(function() {

EasySocial.Controller("Story", {
hostname: "story",
defaultOptions: {
	"plugin": {
		"text": {
			id: "text",
			name: "text",
			selector: "[data-story-plugin-name=photos]",
			type: "panel"
		}
	},

	sourceView: null,

	attachment: {
		limit: 1,
		lifo: true
	},

	flood: {
		enabled: false,
		interval: 90,
		submit: true,
	},

	enterToSubmit: false,
	hashtagEditable: false,
	singlePanel: false,
	emoticons: [],

	// Containers
	"{body}": "[data-body]",
	"{footer}": "[data-footer]",

	"{form}": "[data-story-form]",
	"{textbox}": "[data-story-textbox]",
	"{textField}": "[data-story-textField]",
	"{target}": "[data-story-target]",

	"{cluster}"      : "[data-story-cluster]",
	"{clusterType}"  : "[data-story-clustertype]",
	"{clusterPrivacy}" : "[data-story-clusterprivacy]",

	"{anywhereId}"  : "[data-story-anywhere]",

	"{submitButton}": "[data-story-submit]",
	"{privacyButton}": "[data-story-privacy]",
	"{postAsButton}": "[data-postas-toggle]",

	// Panels
	"{panelContents}": "[data-story-panel-contents]",
	"{panelContent}": "[data-story-panel-content]",
	"{panelButton}": "[data-story-panel-button]",

	"{friends}": "[data-story-friends]",
	"{location}": "[data-story-location]",
	"{mood}": "[data-story-mood]",
	"{autopost}": "[data-story-autopost]",

	// Custom Backgrounds
	"{background}": "[data-background-select]",
	"{currentBackground}": "[data-background-current]",
	"{resetBackground}": "[data-background-reset]",

	// Story params
	"{params}": "[data-story-params]",

	// Mentions
	"{mentionsOverlay}": "[data-mentions-overlay]",
				"{mentionsMetaOverlay}": "[data-mentions-meta-overlay]",

	// Meta
	"{meta}": "[data-story-meta]",
	"{metaContents}": "[data-story-meta-contents]",
	"{metaContent}" : "[data-story-meta-content]",
	"{metaButtons}" : "[data-story-meta-buttons]",
	"{metaButton}"  : "[data-story-meta-button]",

	// Hints
	"{tagHints}": "[data-hints-hashtags]",
	"{friendHints}": "[data-hints-friends]",
	"{emoticonHints}": "[data-hints-emoticons]",

	"{plusButton}": "[data-story-panel-add]"
}

}, function(self, opts, base) { return {

	init: function() {
		// Find out what's my story id
		self.id = base.data("story");

		self.streamId = base.data('stream-id');
		self.isModule = base.data('story-module');

		self.isEdit = false;

		if (self.streamId) {
			self.isEdit = self.streamId;
		}

		if (self.isEdit) {
			self.initMention();
		} else {
			if (opts.flood.enabled && !opts.flood.submit) {
				self.lockSubmitButton(opts.flood.interval);
			}
		}

		var tmpPluginName = '';
		var totalPlugin = 0;

		// Create plugin repository
		$.each(self.options.plugin, function(pluginName, pluginOptions) {

			var plugin = self.plugins[pluginName] = pluginOptions;

			// Pre-count the number of available attachment type
			if (plugin.type=="attachment") {
				self.attachments.max++;
			}

			// Add selector property
			plugin.selector = self.getPluginSelector(pluginName);

			tmpPluginName = pluginName;

			if (self.options.singlePanel && pluginName != 'text') {
				totalPlugin++;
			}
		});

		if ((self.isEdit || totalPlugin == 1) && tmpPluginName != 'text') {
			// lets activite the panel
			self.activatePanel(tmpPluginName);
		}

		// Set mentions
		self.setMentionsLayout();

		// Implement friends plugin
		if (self.friends().length > 0) {
			var clusterId = self.cluster().val();
			var clusterType = self.clusterType().val();
			var privacyType = self.clusterPrivacy().val();
			var options = {};

			// Private cluster should only retrieve the member list
			if (privacyType == 2 || privacyType == 3) {
				namespace = 'site/controllers/friends/suggestClusterMembers';
				options = {
					namespace : namespace,
					clusterId : clusterId,
					clusterType : clusterType
				}
			}

			EasySocial.module("site/story/friends")
				.done(function(){
					self.addPlugin("friends", options);

					if (self.isEdit) {

						var friends = self.friends().find('[data-textboxlist-item]');

						var ids = [];

						friends.each(function(){
							var id = $(this).data("id");

							ids.push(id);
						});

						EasySocial.ajax('site/views/story/buildStoryMeta', {
							"ids": ids
						}).done(function(caption) {
							self.setMeta('friends', caption);
						});
					}

				});
		}

		if (self.location().length > 0) {

			var location = false;

			var currentLocation = self.options.currentLocation;
			var mapElementId = self.options.mapElementId;

			if (self.isEdit && currentLocation) {
				location = currentLocation
			}

			if (self.options.mapIntegration !== 'osm') {
				EasySocial.module("site/story/gmaps")
					.done(function(){
						self.addPlugin("gmaps", {
							currentLocation: location
						});
					});
			} else {
				EasySocial.module("site/story/osm")
				.done(function(){
					self.addPlugin("osm", {
						currentLocation: location,
						mapElementId: mapElementId
					});
				});
			}


		}

		if (self.mood().length > 0) {

			var mood = false;

			var currentMood = self.options.currentMood;

			if (self.isEdit && currentMood) {
				mood = currentMood
			}

			EasySocial.module("site/story/mood")
				.done(function(){
					self.addPlugin("mood", {
						currentMood: mood
					});
				});
		}

		// Remember placeholder value (used by meta)
		self.placeholder = self.textField().attr("placeholder");

		// Duckpunch setMessage
		self._setMessage = self.setMessage;

		self.setMessage = function() {

			// Do not set any messages when story is collapsed or is resizing.
			if (base.hasClass("is-resizing")) {
				return;
			}

			// Remove any previous message group first to avoid stacking error messages.
			this.element
				.find('[data-message-group]')
				.remove();

			self._setMessage.apply(this, arguments);
		};

		// Show placeholder shim for ie9
		if (navigator.userAgent.match(/MSIE 9.0/i)) {
			base.addClass("is-ie");
		}

		// Resolve story instance
		$.module("story-" + self.id).resolve(self);
	},

	"{resetBackground} click": function(element, event) {
		var body = self.body().find('.es-story-text');
		var panel = self.body().find('> .es-story-panel-content');

		body.attr('class', 'es-story-text');
		panel.attr('class', 'es-story-panel-content');

		// Set the active background
		self.currentBackground()
			.attr('class', 'es-story-bg-menu-preview')
			.data('id', '');
	},

	"{background} click": function(element, event) {
		var id = element.data('id');
		var body = self.body().find('.es-story-text');
		var panel = self.body().find('> .es-story-panel-content');

		body.attr('class', 'es-story-text es-story--bg-' + id);
		panel.attr('class', 'es-story-panel-content es-story--bg-' + id);

		// Set the active background
		self.currentBackground()
			.attr('class', 'es-story-bg-menu-preview es-story--bg-' + id)
			.data('id', id);
	},

	"{self} click": function(element, event) {

		if ($(event.target).parents().andSelf().length > 0) {
			return;
		}

		self.expand();
	},

	"{textField} touchstart": function() {
		self.expand();
	},

	"{textField} keydown": function(textField, event) {
		self.expand();
	},

	"{textField} click": function(textField, event) {
		self.expand();

		if (!opts.hashtagEditable) {
			self.preventHashtagEdit(textField, event);
		}
	},

	"{textField} dragstart": function(textField, event) {
		// Prevent drag in textarea
		event.preventDefault();
	},

	"{textField} mousedown": function(textField, event) {
		self.expand();
	},

	"{textField} keydown": function(textField, event) {

		// Bind cmd + enter key to submit
		// If pressing enter submits form
		// And enter key was pressed
		// Without any meta keys involved
		if (((event.metaKey || event.ctrlKey) && event.keyCode == 13) || (opts.enterToSubmit && event.keyCode==13 && !(event.shiftKey || event.altKey || event.ctrlKey || event.metaKey))) {
			self.save();
			event.preventDefault();
		}

		// Prevent the hashtag from being deleted.
		if (!opts.hashtagEditable) {
			self.preventHashtagEdit(textField, event);
		}
	},

	"{textField} paste": function(textField, event) {
		// Prevent the hashtag from being deleted.
		if (!opts.hashtagEditable) {
			self.preventHashtagEdit(textField, event);
		}
	},

	"{textField} focus": function(textField, event) {
		self.setTextboxCaret(textField);
	},

	setTextboxCaret: function(textField) {

		if (self.isEdit) {
			return;
		}

		var value = $(textField).data('default');
		var textvalue = textField.val();

		if (value.length == 0 || value.length != textvalue.length) {
			return;
		}

		var textbox = self.textbox();
		var mentions = textbox.controller("mentions");

		var markers = mentions.getMarkers();

		var lastMarker = null;

		$.each(markers, function() {
			var marker = this;

			if (marker.length >= 1 ) {
				lastMarker = marker;
			}
		});

		if (lastMarker != null) {
			var $textarea = $(lastMarker.textarea);
			var pos = lastMarker.start + lastMarker.length;

			var value = $(textField).data('default');
			var textvalue = textField.val();
			$textarea.caret(pos);
		}
	},

	expand: $.debounce(function() {

		if (base.hasClass("is-expanded") || base.hasClass("is-resizing")) {
			return;
		}
		var transitionEnd = $.support && $.support.transition && $.support.transition.end,
			transitionEvent = (transitionEnd || "transitionend") + ".es.story",
			finalize = $.debounce(function(){

				base.off(transitionEvent)
					.addClass("is-expanded")
					.removeClass("is-resizing")

				// Executes only once
				self.setMentionsLayout();
				self.submitButton().removeAttr("data-disabled");
				self.textField().focus();
			}, 1);

		if (transitionEnd) {
			base.on(transitionEvent, finalize);
		} else {
			setTimeout(finalize, 600);
		}

		// The CSS transition in this class expands the textarea
		base.removeClass("is-collapsed")
			.addClass("is-resizing");
	}, 1),

	preventHashtagEdit: function(textField, event) {
		var value = $(textField).data('default');
		var textbox = self.textbox();
		var mentions = textbox.controller("mentions");
		var markers = mentions.getMarkers();

		var lastMarker = null;

		$.each(markers, function() {
			var marker = this;

			if (marker.length >= 1 ) {
				lastMarker = marker;
			}
		});

		if (lastMarker != null) {
			var $textarea = $(lastMarker.textarea);
			var currentPos = $textarea.caret().start;

			// prevent backspace
			if (event.keyCode == 8 && currentPos == value.length) {
				event.preventDefault();
			}

			// Automatically move the cursor at the end of the tags.
			if (currentPos < value.length) {
				var pos = lastMarker.start + lastMarker.length;
				$textarea.caret(pos);

				textValue = textField.val();
				endPos = $textarea.caret().end;

				// user are trying to delete the entire content
				if (endPos == textValue.length) {
					mentions.reset();
				} else {
					event.preventDefault();
				}
			}
		}

		// Prevent new hashtag from being registered into the content
		if (event.keyCode == 51 && event.shiftKey) {
			event.preventDefault();
		}
	},

	collapse: function() {

		if (base.hasClass("is-collapsed") || base.hasClass("is-resizing")) {
			return;
		}

		base.addClass("is-resizing")
			.removeClass("is-expanded");

		// Reset the text
		self.clear();
		self.textField().blur();

		setTimeout(function(){
			base.addClass("is-collapsed")
				.removeClass("is-resizing");
		}, 1);
	},

	initMention: function() {

		var textbox = self.find('[data-story-textbox]');
		var mentions = textbox.controller("mentions");

		if (mentions) {
			mentions.cloneLayout();
			// return;
		} else {

			// Try to find for hints that may already be available on the page.
			var hashtagsHints = $('[data-hints-hashtags]');
			var friendsHints = $('[data-hints-friends]');
			var emoticonsHints = $('[data-hints-emoticons]');
			var autocompleteHints = $('[data-hints-autocomplete]');

			// override the autocomplete hint
			$.template("mentions/xsearchHint", autocompleteHints.find('[data-search]').html());
			$.template("mentions/xemptyHint", autocompleteHints.find('[data-empty]').html());

			textbox
				.mentions({
					triggers: {
						"@": {
							type: "entity",
							wrap: false,
							stop: "",
							allowSpace: true,
							finalize: true,
							query: {
								loadingHint: true,
								searchHint: friendsHints.find('[data-search]'),
								emptyHint: friendsHints.find('[data-empty]'),
								data: function(keyword) {

									var task = $.Deferred();
									var namespace = 'site/controllers/friends/suggest';
									var options = {search: keyword};
									var clusterId = self.cluster().val();

									// Cluster mention
									if (clusterId) {
										var privacyType = self.clusterPrivacy().val();

										// Private cluster should only retrieve the member list
										if (privacyType == 2 || privacyType == 3) {
											namespace = 'site/controllers/friends/suggestClusterMembers';
											options = {
												search: keyword,
												clusterId : clusterId,
												clusterType : self.clusterType().val()
											}
										}
									}

									EasySocial.ajax(namespace, options)
										.done(function(items){

											if (!$.isArray(items)) {
												task.reject();
											}

											var items = $.map(items, function(item){

												var html = $('<div/>').html(item);
												var title = html.find('[data-suggest-title]').val();
												var id = html.find('[data-suggest-id]').val();

												return {
													"id": id,
													"title": title,
													"type": "user",
													"menuHtml": item
												};
											});

											task.resolve(items);
										})
										.fail(task.reject);

									return task;
								},
								use: function(item) {
									return item.type + ":" + item.id;
								}
							}
						},
						"#": {
							type: "hashtag",
							wrap: true,
							stop: " #",
							allowSpace: false,
							query: {
								loadingHint: true,
								searchHint: hashtagsHints.find('[data-search]'),
								emptyHint: hashtagsHints.find('[data-empty]'),
								data: function(keyword) {

									var task = $.Deferred();

									EasySocial.ajax("site/controllers/hashtags/suggest", {search: keyword})
										.done(function(items){

											if (!$.isArray(items)) {
												task.reject();
											}

											var items = $.map(items, function(item) {

												return {
													"title": "#" + item,
													"type": "hashtag",
													"menuHtml": item
												};
											});

											task.resolve(items);
										})
										.fail(task.reject);

									return task;
								}
							}
						},
						":": {
							type: "emoticon",
							wrap: true,
							stop: "",
							allowSpace: false,
							query: {
								loadingHint: true,
								searchHint: emoticonsHints.find('[data-search]').html(),
								emptyHint: emoticonsHints.find('[data-empty]').html(),
								data: $.parseJSON(self.options.emoticons),
								renderAll: true
							}
						}
					},
					plugin: {
						autocomplete: {
							id: "es",
							component: "es",
							modifier: "es-story-mentions-autocomplete",
							sticky: true,
							shadow: true,
							position: {
								my: 'left top',
								at: 'left bottom',
								of: self.find('.es-story-text'),
								collision: 'none'
							},
							view: {
								searchHint: "mentions/xsearchHint",
								emptyHint: "mentions/xemptyHint",
							}
						}
					}
				});
		}
	},

	setMentionsLayout: function() {

		var textbox = self.textbox();
		var mentions = textbox.controller("mentions");

		if (mentions) {
			var mentionedItems = self.options.mentionedItems;

			if (self.isEdit && mentionedItems) {

				var markers = mentions.getMarkers();

				$.each(markers, function() {

					var marker = this;

					$.each(mentionedItems, function() {
						var mention = this;

						if (marker.start == mention.start && marker.length == mention.length) {

							var text = marker.val();

							var textValue = 'user' + ':' + mention.userId;
							var trigger = mentions.getTriggerFromType('entity');

							if (text.charAt(0) == '#') {
								var trigger = mentions.getTriggerFromType('hashtag');
								var textValue = text.substring(1);
							}

							if (text.charAt(0) == ':') {
								var trigger = mentions.getTriggerFromType('emoticon');
								var textValue = text.substring(1);
							}

							var data = $(marker.block).data("marker");
							data.trigger = trigger;

									// update marker's value
									marker.updateValue(textValue);
						}
					});
				});
			}

			mentions.cloneLayout();
			return;
		}


		var body = self.body();

		textbox.mentions({
			"triggers": {
				"@": {
					"type": "entity",
					"wrap": false,
					"stop": "",
					"allowSpace": true,
					"finalize": true,
					"query": {

							loadingHint: true,
							searchHint: self.friendHints().find('[data-search]').html(),
							emptyHint: self.friendHints().find('[data-empty]').html(),
							data: function(keyword) {

								var task = $.Deferred();
								var namespace = 'site/controllers/friends/suggest';
								var options = {search: keyword};
								var clusterId = self.cluster().val();

								// Cluster mention
								if (clusterId) {
									var privacyType = self.clusterPrivacy().val();

									// Private cluster should only retrieve the member list
									if (privacyType == 2 || privacyType == 3) {
										namespace = 'site/controllers/friends/suggestClusterMembers';
										options = {
											search: keyword,
											clusterId : clusterId,
											clusterType : self.clusterType().val()
										}
									}
								}

								EasySocial.ajax(namespace, options)
								.done(function(items) {

									if (!$.isArray(items)) {
										task.reject();
										return;
									}

									var items = $.map(items, function(item){

										var html = $('<div/>').html(item);
										var title = html.find('[data-suggest-title]').val();
										var id = html.find('[data-suggest-id]').val();

										return {
											"id": id,
											"title": title,
											"type": "user",
											"menuHtml": item
										};
									});

									task.resolve(items);

								}).fail(task.reject);

								return task;
							},
							use: function(item) {
								return item.type + ":" + item.id;
							}
						}
					},
					"#": {
						type: "hashtag",
						wrap: true,
						stop: " #",
						allowSpace: false,
						query: {
							loadingHint: true,
							searchHint: self.tagHints().find('[data-search]').html(),
							emptyHint: self.tagHints().find('[data-empty]').html(),
							data: function(keyword) {

								var task = $.Deferred();

								EasySocial.ajax("site/controllers/hashtags/suggest", {
									"search": keyword
								}).done(function(items){

									if (!$.isArray(items)) {
										task.reject();
										return;
									}

									var items = $.map(items, function(item) {

										return {
											"title": "#" + $.trim(item),
											"type": "hashtag",
											"menuHtml": item
										};
									});

									task.resolve(items);

								}).fail(task.reject);

								return task;
							}
						}
					},
					":": {
						type: "emoticon",
						wrap: true,
						stop: "",
						allowSpace: false,
						query: {
							loadingHint: true,
							searchHint: self.emoticonHints().find('[data-search]').html(),
							emptyHint: self.emoticonHints().find('[data-empty]').html(),
							data: $.parseJSON(self.options.emoticons),
							renderAll: true
						}
					}
				},
				plugin: {
					autocomplete: {
						id: "es",
						component: "",
						modifier: "es-story-mentions-autocomplete",
						sticky: true,
						shadow: true,
						position: {
							my: 'left top',
							at: 'left bottom',
							of: textbox.parent(),
							collision: 'none'
						},
						size: {
							width: function() {
								return body.width();
							}
						}
					}
				}
			});
	},

	//
	// PLUGINS
	//
	plugins: {},

	getPluginName: function(element) {
		return $(element).data("story-plugin-name");
	},

	getPluginSelector: function(pluginName) {
		return "[data-story-plugin-name=" + pluginName + "]";
	},

	hasPlugin: function(pluginName, pluginType) {

		var plugin = self.plugins[pluginName];

		if (!plugin) return false;

		// Also check for pluginType
		if (pluginType) return (plugin.type===pluginType);

		return true;
	},

	buildPluginSelectors: function(selectorNames, plugin, pluginControllerType) {

		var selectors = {};

		$.each(selectorNames, function(i, selectorName) {

			var selector = self[selectorName].selector + plugin.selector;

			if (pluginControllerType=="function") {
				selectors[selectorName] = function() {
					return self.find(selector);
				};
			} else {
				selectors["{"+selectorName+"}"] = selector;
			}
		});

		return selectors;
	},

	"{self} addPlugin": function(element, event, pluginName, pluginController, pluginOptions, pluginControllerType) {

		// Prevent unregistered plugin from extending onto story
		if (!self.hasPlugin(pluginName))
		{
			return;
		}

		var plugin = self.plugins[pluginName],
			extendedOptions = {};

		// See plugin type and build the necessary options for them
		switch (plugin.type)
		{
			case "panel":
				var panelSelectors = [
					"panelButton",
					"panelContent"
				];
				extendedOptions = self.buildPluginSelectors(panelSelectors, plugin, pluginControllerType);
				break;
		}

		$.extend(pluginOptions, extendedOptions);
	},

	"{self} registerPlugin": function(element, event, pluginName, pluginInstance) {

		// Prevent unregistered plugin from extending onto story
		if (!self.hasPlugin(pluginName)) return;

		var plugin = self.plugins[pluginName];

		plugin.instance = pluginInstance;
	},

	//
	// PANELS
	//

	panels: {},

	currentPanel: "text",

	getPanel: function(pluginName) {

		// If plugin is not a panel, stop.
		if (!self.hasPlugin(pluginName, 'panel')) return;

		var plugin = self.plugins[pluginName];

		// Return existing panel entry if it has been created,
		return self.panels[plugin.name] ||

				// or create panel entry and return it.
				(self.panels[plugin.name] = {
					plugin: plugin,
					button: self.panelButton(plugin.selector),
					content: self.panelContent(plugin.selector)
				});
	},

	activatePanel: function(pluginName) {

		// Get panel
		var panel = self.getPanel(pluginName);

		// If panel does not exist, stop.
		if (!panel) return;

		// Deactivate current panel
		self.deactivatePanel(self.currentPanel);

		// if (self.postAsButton().length > 0) {
		// 	// remove disabled class for the Post As dropdown
		// 	self.postAsButton().removeClass("disabled");

		// 	// Check if need to disable the dropdown
		// 	self.disablePostAsDropdown(pluginName);
		// }

		// Set plugin as current panel
		self.currentPanel = pluginName;

		var panelContents = self.panelContents();

		// Activate submit button (just in case it is disabled)
		self.submitButton().removeAttr("disabled");

		// Activate panel container
		panelContents.addClass("active");

		// Activate panel
		panel.button.addClass("active");
		panel.content
			.appendTo(panelContents)
			.addClass("active");

		base.addClass("plugin-" + pluginName);

		// Invoke plugin's activate method if exists
		self.invokePlugin(pluginName, "activatePanel", [panel]);

		// Trigger panel activate event
		self.trigger("activatePanel", [pluginName]);

		// Refocus story form
		self.textField().focus();
	},

	deactivatePanel: function(pluginName) {

		// Get panel
		var panel = self.getPanel(pluginName);

		// If panel does not exist, stop.
		if (!panel) return;

		// Deactivate panel
		panel.button.removeClass("active");
		panel.content.removeClass("active");

		base.removeClass("plugin-" + pluginName);

		// Deactivate panel container
		self.panelContents().removeClass("active");

		// Invoke plugin's deactivate method if exists
		self.invokePlugin(pluginName, "deactivatePanel", [panel]);

		// Trigger panel deactivate event
		self.trigger("deactivatePanel", [pluginName]);
	},

	// disablePostAsDropdown: function(pluginName) {

	// 	var plugins = ["event", "files", "blog"];

	// 	// This is to make sure post as page is selected by default
	// 	$('[data-postas-page]').click();

	// 	if ($.inArray(pluginName, plugins)  > -1){

	// 		// For blog post, we only allow post as user
	// 		if (pluginName == 'blog') {
	// 			$('[data-postas-user]').click();
	// 		}

	// 		self.postAsButton().addClass("disabled");
	// 	}
	// },

	addPanelCaption: function(pluginName, panelCaption) {

		// Get panel
		var panel = self.getPanel(pluginName);

		// If panel does not exist, stop.
		if (!panel) return;

		panel.button
			.addClass("has-data")
			.find(".with-data").html(panelCaption);
	},

	removePanelCaption: function(pluginName) {

		// Get panel
		var panel = self.getPanel(pluginName);

		// If panel does not exist, stop.
		if (!panel) return;

		panel.button
			.removeClass("has-data")
			.find(".with-data").empty();
	},

	// Triggered when the panel buttons beneath the story footer is clicked
	"{panelButton} click": function(panelButton, event) {
		var pluginName = self.getPluginName(panelButton);
		self.activatePanel(pluginName);
	},

	"{plusButton} click": function(plusButton, event) {
		// we need to set the current controller so that in wireframe/story/default.js the buttons can
		// retrieve the correct controller.
		window.currentStoryController = self;
	},

	//
	// SAVING
	//
	saving: false,
	locked: false,

	save: function(isEdit) {

		if (self.saving) {
			return;
		}

		if (self.locked) {
			EasySocial.dialog({
				"content": EasySocial.ajax('site/views/story/showFloodWarning')
			});
			return;
		}

		self.saving = true;

		// Create save object
		var save = $.Deferred();

		save.data = {};
		save.tasks = [];

		save.addData = function(plugin, props) {

			var pluginName = plugin.options.name;
			var pluginType = plugin.options.type;

			if (pluginName !== self.currentPanel) {
				return;
			}

			save.data.attachment = self.currentPanel;

			if ($.isPlainObject(props)) {
				$.each(props, function(key, val) {
					save.data[pluginName + "_" + key] = val;
				});
			} else {
				save.data[pluginName] = props;
			}
		};

		save.addTask = function(name) {

			var task = $.Deferred();
			task.name = name;
			task.save = save;
			save.tasks.push(task);
			return task;
		};

		save.process = function() {

			if (save.state() === "pending") {
				$.when.apply($, save.tasks)
					.done(function() {

						// If content & attachment is empty, reject.
						if (!$.trim(save.data.content) && !save.data.attachment) {

							save.reject(opts.errors.empty, "warning");
							return;
						}

						save.resolve();
					})
					.fail(save.reject);
			}

			return save;
		};

		save.data.isEdit = isEdit;

		// Set the current panel so that the plugins know whether they should intercept
		save.currentPanel = self.currentPanel;

		// Trigger the save event
		self.trigger("save", [save]);

		self.element.addClass("saving");

		save.process()
			.done(function(){

				var mentions = self.textbox().mentions("controller").toArray();
				var hashtags = self.element.data("storyHashtags");
				var nohashtags = false;
				hashtags = (hashtags) ? hashtags.split(",") : [];

				if (hashtags.length > 0) {
					var tags =
						$.map(mentions, function(mention) {
							if (mention.type==="hashtag" && $.inArray(mention.value, hashtags) > -1) {
								return mention;
							}
						});

					nohashtags = tags.length < 1;
				}

				self.trigger("beforeSubmit", [save]);

				// Save the background id
				save.data.backgroundId = self.currentBackground().data('id');

				// Find any auto post input
				var autopost = self.autopost().find('input[type=checkbox]:checked');
				save.data.autopost = [];

				if (autopost.length > 0) {
					autopost.each(function(i, item) {
						var item = $(item);
						var name = item.attr('name');
						var checked = item.is(':checked');

						if (checked) {
							save.data[name] = 1;
						}

					});
				}

				// Get custom params
				var params = self.params();

				if (params.length > 0) {
					$.each(params, function(i, param) {
						var name = $(this).attr('name');
						var value = $(this).val();

						save.data[name] = value;
					});
				}

				// then the ajax call to save story.
				EasySocial.ajax("site/controllers/story/create", save.data)
					.done(function(html, id, message, preview, backgroundId, locationPreview) {

						// If this isEdit
						if (isEdit) {
							self.trigger("update", [html, id, preview, backgroundId, locationPreview]);

						} else {
							if (nohashtags) {
								html = self.setMessage(opts.errors.filter);
							}

							self.trigger("create", [html, id]);
							self.clear();

							if (opts.flood.enabled) {
								self.lockSubmitButton(opts.flood.interval);
							}
						}

						// Display success message for quickpost module
						if (self.isModule && message) {
							self.setMessage(message.message, message.type);
						}

						// Initialize reactions
						if (window.es.mobile || window.es.tablet) {
							window.es.initReactions();
						}

					})
					.fail(function(message){
						self.trigger("fail", arguments);

						var msg = message;
						var msgType = 'warning';

						if ($.type(message) !== 'string') {
							msg = message.message;
							msgType = message.type;
						}


						self.setMessage(msg, msgType);
					})
					.always(function(){

						self.trigger("afterSubmit", [save]);

						self.element.removeClass("saving");
						self.saving = false;
					});
			})
			.fail(function(message, messageType){

				if (!message) {
					message = opts.errors.standard;
					messageType = "error";
				}

				self.setMessage(message, messageType);
				self.element.removeClass("saving");
				self.saving = false;
			});
	},

	lockSubmitButton: function(interval) {

		// determine if we need to lock the submit button or not.
		if (opts.flood.enabled && interval > 0) {

			self.locked = true;

			var delay = interval * 1000;

			// need to start the counter.
			setTimeout(function() {
				self.locked = false;
			}, delay);
		}

	},


	clear: function() {

		// Clear textfield
		self.textField().val("");

		// Clear status messages
		self.clearMessage();

		// Reactivate text panel
		// Only reactivate the panel if this is not a single panel
		if (!self.options.singlePanel) {
			self.activatePanel("text");
		}

		// Deactivate meta
		self.deactivateMeta(self.currentMeta);

		// Trigger clear event
		self.trigger("clear");

		// Reset mentions
		var mentions = self.textbox().mentions("controller");
		mentions.reset();

		setTimeout(function(){
			mentions.cloneLayout();
		}, 500);

		// Focus textfield
		self.textField().focus();

		self.resetBackground().click();
	},

	getPostActor: function() {
		var postAsHidden = $('[data-postas-base] [data-postas-hidden]');

		return postAsHidden.val();
	},

	"{self} save": function(element, event, save) {

		var content = self.textField().val();
		var data = save.data;

		data.view = self.options.sourceView;
		data.content = content;
		data.target  = self.target().val();
		data.cluster = self.cluster().val();
		data.clusterType = self.clusterType().val();
		data.anywhereId = self.anywhereId().val();
		data.pageTitle = window.document.title;
		data.postActor = self.getPostActor();
		data.privacy = self.find("[data-privacy-hidden]").val();
		data.privacyCustom = self.find("[data-privacy-custom-hidden]").val();
		data.privacyField = '';


		var tmp = [];
		self.find("[data-privacy-field-inputs]").each(function(idx, ele) {
			var select = $(ele);
			var key = select.attr('name');
			var values = select.val();

			if (values) {
				key = key + '|' + values.join(',');
				tmp.push(key);
			}
		});

		if (tmp) {
			var value = tmp.join(';');
			data.privacyField = value;
		}


		var mentions = self.textbox().mentions("controller").toArray();

		data.mentions = $.map(mentions, function(mention){
			if ((mention.type==="hashtag" || mention.type==="emoticon") && $.isPlainObject(mention.value)) {
				mention.value = mention.value.title.slice(1);
			}
			return JSON.stringify(mention);
		});
	},

	"{submitButton} click": function(submitButton, event) {
		self.save(self.isEdit);
	},

	//
	// Privacy
	//
	"{privacyButton} click": function(el) {
		setTimeout(function(){
			var isActive = el.find("[data-es-privacy-container]").hasClass("active");
		}, 1);
	},

	//
	// Meta
	//
	metas: {
		friends: "",
		location: "",
		mood: ""
	},

	currentMeta: null,

	getMeta: function(metaName) {

		var meta = {
			name: metaName,
			button: self.metaButton().filterBy("storyMetaButton", metaName),
			content: self.metaContent().filterBy("storyMetaContent", metaName)
		};

		if (meta.button.length < 1 || meta.content.length < 1) return null;

		return meta;
	},

	activateMeta: function(metaName) {

		var meta = self.getMeta(metaName);

		if (!meta) return;

		// Deactivate current meta
		self.deactivateMeta(self.currentMeta);

		meta.button.addClass("active");

		// Always push meta content to the beginning
		meta.content
			.appendTo(self.metaContents())
			.addClass("active");

		self.currentMeta = metaName;

		self.trigger("activateMeta", [meta]);

		base.addClass("has-meta");
	},

	deactivateMeta: function(metaName) {

		var meta = self.getMeta(metaName);

		if (!meta) return;

		meta.button.removeClass("active");

		meta.content.removeClass("active");

		self.currentMeta = null;

		self.trigger("deactivateMeta", [meta]);

		base.removeClass("has-meta");
	},

	toggleMeta: function(metaName) {

		if (self.currentMeta == metaName) {
			self.deactivateMeta(metaName);
		} else {
			self.activateMeta(metaName);
		}
	},

	getMetaText: function() {

		var metas = self.metas;
		var parts = [];

		$.each(["friends", "location", "mood"], function(i, type){
			var meta = metas[type];
			if (meta) {
				parts.push(meta);
			}
		});

		return parts.join(' ');
	},

	setMeta: function(metaName, content) {

		self.metas[metaName] = content;
		self.updateMeta();
	},

	updateMeta: $.debounce(function() {

		// This is debounced so we only have to update
		// the html once after multiple setMeta calls.
		var metaText = self.getMetaText(),
			meta = self.meta(),
			textField = self.textField();

		// Highlight meta button icon if meta has content
		$.each(self.metas, function(key, val){
			var meta = self.getMeta(key);
			meta && meta.button.toggleClass("has-content", !!val);
		});

		// If there is no meta string, don't show anything.
		if (!metaText) {
			meta.remove();
			textField.attr("placeholder", self.placeholder);
			return;
		}

		// Create meta element if it does not exist;
				var mentionsOverlay = self.mentionsMetaOverlay();

		if (meta.length < 1) {
			meta = $('<u class="es-story-meta" data-story-meta data-ignore></u>').appendTo(mentionsOverlay);
		}

		// Add rtl mark if necessary
		var rtlMark =  mentionsOverlay.css("direction")=="rtl" ? "&#8207;" : "";

		// Update meta string
		meta.html(rtlMark + " &mdash; " + metaText);

		// Don't show placeholder text if we have meta text
		textField.attr("placeholder", "");

		self.setMentionsLayout();

	}, 1),

	refreshMeta: function() {

		// Trigger refresh meta so plugins
		// can update the meta the database
		self.trigger("refreshMeta");

		self.updateMeta();
	},

	"{textbox} triggerClear": function() {

		self.refreshMeta();
	},

	"{meta} click": function(meta, event) {

		// Do not focus textfield if a link was clicked
		if ($(event.target).is("a")) return;

		self.textField().focus();
	},

	"{metaButton} click": function(metaButton) {

		var metaName = metaButton.attr("data-story-meta-button");
		self.toggleMeta(metaName);
	}
}});

module.resolve();
});

});
