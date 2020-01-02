EasySocial.module('site/followers/filter', function($) {

var module = this;

EasySocial.require()
.script('site/followers/browser')
.done(function($) {

EasySocial.Controller('Followers.Filter', {
	defaultOptions: {
		"{filter}": "[data-es-followers-filters] [data-filter-item]",
		"{browserController}": "[data-es-followers]"
	}
}, function(self, opts) { return {

	getController: function() {
		return self.browserController().controller();
	},

	setActiveFilter: function(item) {
		self.filter().removeClass('active');

		item.addClass('is-loading active');
	},

	"{filter} click": function(element, event) {
		var controller = self.getController();

		if (controller === undefined) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		var anchor = element.find('> a');
		anchor.route();

		var type = element.data('type');
		var id = element.data('id');

		self.setActiveFilter(element);

		controller.getFollowers(type, function() {
			element.removeClass('is-loading');
		});
	}
}});

module.resolve();

});
});
