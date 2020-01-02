EasySocial.module('admin/header/apps', function($) {

var module = this;

EasySocial.require()
.library('ui/sortable', 'scrollTo')
.done(function($) {

EasySocial.Controller("Header.Apps", {
	defaultOptions: {
		"{appsWrapper}": '[data-apps-wrapper]',
		"{apps}" : '[data-apps]',
		"{appItem}" : '[data-app-item]'
	}
}, function(self, opt, base) { return {

	init : function() {
		self.initAppsSortable();
		self.updateOrdering(self.appItem());
	},

	initAppsSortable: function() {
		self.apps().sortable({
			items: self.appItem.selector,
			cursor: 'move',
			forceHelperSize: true,
			axis : 'y',
			cancel : '.do-not-move',
			start: function(event, ui) {
				ui.helper.addClass('is-new');
			},
			stop: function(event, ui) {
				setTimeout(function() {
					ui.item.removeClass('is-new');
				}, 150);
			},
			update : function(event, ui) {
				self.updateOrdering(self.appItem());
			}
		});
	},

	updateOrdering: function(element) {
		var appOrdering = [];

		$.each(element, function() {
			var item = $(this),
				element = item.data('element'),
				index = item.index();

			// Update the sequence
			item.attr('data-ordering', index);

			appOrdering.push(element);
		});

		appOrdering = JSON.stringify(appOrdering);
		$('[data-app-ordering-value]').val(appOrdering);
	}
}});

module.resolve();
});
});
