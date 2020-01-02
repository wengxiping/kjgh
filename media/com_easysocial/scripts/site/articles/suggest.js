EasySocial.module('site/articles/suggest', function($){

var module = this;

EasySocial.require()
.library('textboxlist')
.done(function($) {

EasySocial.Controller('Articles.Suggest', {
	defaultOptions: {
		max: null,
		exclusive: true,
		exclusion: [],
		minLength: 1,
		highlight: true,
		uid: "",
		name: "article_id",
		type: "",

		// Namespace to query for suggestions
		"query": {
			"articles": "admin/controllers/articles/suggest"
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
											"inputName": opts.name
										};

							return EasySocial.ajax(opts.query.articles, options);
						}
					}
				}
			})

			self.element.textboxlist('enable');

			// Search for saved value
			var savedValue = self.element.find('[data-textboxlist-item]').data('title');

			if (savedValue) {
				self.element.find('[data-textboxlist-textField]').addClass('t-hidden');
				self.element.textboxlist('disable');
			}
	},

	"{self} filterItem": function(el, event, item) {

		var html = $('<div/>').html(item.html);
		var wrapper = html.find('[data-suggest]');

		var title = wrapper.data('suggest-title');
		var id = wrapper.data('suggest-id');

		item.id = id;
		item.title = title;
		item.menuHtml = item.html;
	},

	"{self} addItem": function(el, event, item) {
		var input = self.element.find('[data-fields-config-param]');

		input.val(item.id);
		input.trigger('change');

		self.element.textboxlist("disable");
	},

	"{self} removeItem": function(el, event, item) {
		var input = self.element.find('[data-fields-config-param]');

		input.val('');
		input.trigger('change');

		self.element.find('[data-textboxlist-textField]').removeClass('t-hidden');
		self.element.textboxlist("enable");		
	}
}});

module.resolve();
});

});

