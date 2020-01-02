EasySocial.module('site/friends/suggest', function($){

var module = this;

EasySocial.require()
.library('textboxlist')
.done(function($) {

EasySocial.Controller('Friends.Suggest', {
	defaultOptions: {
		max: null,
		exclusive: true,
		exclusion: [],
		minLength: 1,
		highlight: true,
		name: "uid[]",
		type: "",

		// Namespace to query for suggestions
		"query": {
			"friends": "site/controllers/friends/suggest",
			"list": "site/controllers/friends/suggestWithList",
			"clusters": "site/controllers/friends/suggestClusterMembers"
		},

		// Search for friend list as well
		friendList: false,
		friendListName: "",

		includeSelf: false,
		showNonFriend: false,
		privacyRule: "",

		// Add support to search for cluster members
		clusterId: false,
		clusterType: false
	}
}, function(self, opts, base) { return {

	init: function() {

		// Implement the textbox list on the implemented element.
		var autocompleteOptions = {
			"exclusive": opts.exclusive,
			"minLength": opts.minLength,
			"highlight": opts.highlight,
			"showLoadingHint": true,
			"showEmptyHint": true,

			query: function(keyword) {

				var options = {
						"search": keyword,
						"type": opts.type,
						"showNonFriend": opts.showNonFriend,
						"inputName": opts.name,
						"clusterId": opts.clusterId,
						"clusterType": opts.clusterType,
						"privacyRule": opts.privacyRule
					};

				if (opts.includeSelf) {
					options.includeme = true;
				}

				if (opts.clusterId) {
					return EasySocial.ajax(opts.query.clusters, options);
				}

				// Search for normal friends
				if (!opts.friendList) {
					return EasySocial.ajax(opts.query.friends, options);
				}

				// Suggest friend list
				return EasySocial.ajax(opts.query.list, {
					"search": keyword,
					"inputName": opts.name,
					"friendListName": opts.friendListName,
					"showNonFriend": opts.showNonFriend,
					"privacyRule": opts.privacyRule,
					"type": opts.type
				});
			}
		};

		if (opts.emptyMessage) {
			autocompleteOptions.emptyMessage = opts.emptyMessage;
		}

		self.element
			.textboxlist({
				"component": 'es',
				"name": opts.name,
				"max": opts.max,
				"plugin": {
					"autocomplete": autocompleteOptions
				}
			})
			.textboxlist("enable");
	},

	"{self} filterItem": function(el, event, item) {

		// If this suggest searches for friend list, we don't want to format the item result here.
		if (opts.friendList) {
			return;
		}

		var html = $('<div/>').html(item.html);
		var title = html.find('[data-suggest-title]').text();
		var id = html.find('[data-suggest-id]').val();

		item.id = id;
		item.title = title;
		item.menuHtml = item.html;
	},

	"{self} filterMenu": function(el, event, menu, menuItems, autocomplete, textboxlist) {

		// If this suggest searches for friend list, we don't want to format the item result here.
		if (opts.friendList) {
			return;
		}

		// Get list of excluded users
		var items = textboxlist.getAddedItems();
		var users = $.pluck(items, "id");
		var users = users.concat(self.options.exclusion);

		menuItems.each(function(){

			var menuItem = $(this);
			var item = menuItem.data("item");

			// If this user is excluded, hide the menu item
			menuItem.toggleClass("hidden", $.inArray(item.id.toString(), users) > -1);
		});
	}

}});

module.resolve();
});

});

