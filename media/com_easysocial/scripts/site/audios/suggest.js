EasySocial.module('site/audios/suggest', function($){

var module = this;

EasySocial.require()
.library('textboxlist')
.done(function($) {

EasySocial.Controller('Audios.Suggest', {
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
			"audios": "site/controllers/audios/suggest"
		}
	}
}, function(self, opts, base) { return {

	init: function() {

		// Implement the textbox list on the implemented element.
		self.element
			.textboxlist({
				"component": 'es',
				"name": opts.name,
				"max": opts.max,
				"plugin": {
					"autocomplete": {
						"exclusive": opts.exclusive,
						"minLength": opts.minLength,
						"highlight": opts.highlight,
						"showLoadingHint": true,
						"showEmptyHint": true,

						query: function(keyword) {

							var options = {
									"search": keyword,
									"type": opts.type,
									"inputName": opts.name
								};

							return EasySocial.ajax(opts.query.audios, options);
						}
					}
				}
			})
			.textboxlist("enable");
	},

	"{self} filterItem": function(el, event, item) {

		var html = $('<div/>').html(item.html);
		var title = html.find('[data-suggest-title]').text();
		var id = html.find('[data-suggest-id]').val();

		item.id = id;
		item.title = title;
		item.menuHtml = item.html;
	},

	"{self} filterMenu": function(el, event, menu, menuItems, autocomplete, textboxlist) {
		// Get list of excluded audios
		var items = textboxlist.getAddedItems();
		var audios = $.pluck(items, "id");
		var audios = audios.concat(self.options.exclusion);

		menuItems.each(function(){

			var menuItem = $(this);
			var item = menuItem.data("item");

			// If this user is excluded, hide the menu item
			menuItem.toggleClass("hidden", $.inArray(item.id.toString(), audios) > -1);
		});
	}

}});

module.resolve();
});

});

